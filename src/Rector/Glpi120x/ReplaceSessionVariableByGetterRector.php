<?php

declare(strict_types=1);

namespace RectorGlpi\Rector\Glpi120x;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Unset_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ReplaceSessionVariableByGetterRector extends AbstractRector
{
    /**
     * Attribute set on the `$_SESSION[...]` fetches that must not be replaced (written, passed by reference, ...).
     */
    private const SKIP_ATTRIBUTE = 'glpi_session_getter_skip';

    /**
     * Session keys that can be replaced by a `Session` getter.
     *
     * `default` is the value returned by the getter when the key is not set in the session.
     *
     * @var array<string, array{method: string, default: false|0|null|array{}}>
     */
    private const MAPPING = [
        'glpiID'             => ['method' => 'getLoginUserID', 'default' => false],
        'glpilanguage'       => ['method' => 'getLanguage', 'default' => null],
        'glpi_currenttime'   => ['method' => 'getCurrentTime', 'default' => null],
        'glpiactive_entity'  => ['method' => 'getActiveEntity', 'default' => 0],
        'glpiactiveentities' => ['method' => 'getActiveEntities', 'default' => []],
    ];

    public function __construct(
        private readonly ReflectionResolver $reflection_resolver,
    ) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace direct `$_SESSION` reads by the corresponding `Session` getter.'
            . ' Note that `Session::getLoginUserID()` and `Session::isAuthenticated()` return the inventory user'
            . ' while an inventory is running, when `$_SESSION[\'glpiID\']` would not.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        $users_id = $_SESSION['glpiID'];
                        $is_logged = isset($_SESSION['glpiID']);
                        $entity = $_SESSION['glpiactive_entity'];
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        $users_id = \Session::getLoginUserID();
                        $is_logged = \Session::isAuthenticated();
                        $entity = \Session::getActiveEntity();
                        CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [
            // Replaced nodes.
            ArrayDimFetch::class,
            Coalesce::class,
            Empty_::class,
            Isset_::class,

            // Contexts in which the `$_SESSION[...]` fetches must be preserved.
            Assign::class,
            AssignOp::class,
            AssignRef::class,
            PreInc::class,
            PreDec::class,
            PostInc::class,
            PostDec::class,
            Unset_::class,
            Foreach_::class,
            FuncCall::class,
            MethodCall::class,
            StaticCall::class,
            InterpolatedString::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        // Parent nodes are visited before their children, so contexts are detected here and the fetches they
        // contain are flagged before being visited.
        if ($node instanceof Assign || $node instanceof AssignOp) {
            $this->markAsSkipped($node->var);
            return null;
        }
        if ($node instanceof AssignRef) {
            $this->markAsSkipped($node->var);
            $this->markAsSkipped($node->expr);
            return null;
        }
        if ($node instanceof PreInc || $node instanceof PreDec || $node instanceof PostInc || $node instanceof PostDec) {
            $this->markAsSkipped($node->var);
            return null;
        }
        if ($node instanceof Unset_) {
            foreach ($node->vars as $var) {
                $this->markAsSkipped($var);
            }
            return null;
        }
        if ($node instanceof Foreach_) {
            if ($node->byRef) {
                // `foreach ($_SESSION['glpiactiveentities'] as &$entity)` modifies the session.
                $this->markAsSkipped($node->expr);
            }
            $this->markAsSkipped($node->valueVar);
            if ($node->keyVar !== null) {
                $this->markAsSkipped($node->keyVar);
            }
            return null;
        }
        if ($node instanceof FuncCall || $node instanceof MethodCall || $node instanceof StaticCall) {
            $this->markByRefArgumentsAsSkipped($node);
            return null;
        }
        if ($node instanceof InterpolatedString) {
            // Occurences inside string interpolations (e.g. `"User: {$_SESSION['glpiID']}"`) cannot be replaced
            // since a static call is not allowed there.
            foreach ($node->parts as $part) {
                if (!($part instanceof InterpolatedStringPart)) {
                    $this->markAsSkipped($part);
                }
            }
            return null;
        }

        if ($this->isInsideSessionClass($node)) {
            // `Session` getters are reading the session themselves, replacing them would produce infinite recursions.
            return null;
        }

        if ($node instanceof Isset_ || $node instanceof Empty_) {
            return $this->refactorIssetOrEmpty($node);
        }

        if ($node instanceof Coalesce) {
            return $this->refactorCoalesce($node);
        }

        if ($node instanceof ArrayDimFetch) {
            if ($node->getAttribute(self::SKIP_ATTRIBUTE) === true) {
                return null;
            }

            $key = $this->getMappedSessionKey($node);
            if ($key === null) {
                return null;
            }

            return $this->createGetterCall(self::MAPPING[$key]['method']);
        }

        return null;
    }

    /**
     * `isset($_SESSION['glpiID'])` -> `Session::isAuthenticated()`
     * `empty($_SESSION['glpiID'])` -> `Session::isAuthenticated() === false`
     */
    private function refactorIssetOrEmpty(Isset_|Empty_ $node): ?Node
    {
        $vars = $node instanceof Isset_ ? $node->vars : [$node->expr];

        if (
            \count($vars) === 1
            && $vars[0] instanceof ArrayDimFetch
            && $this->getMappedSessionKey($vars[0]) === 'glpiID'
        ) {
            $is_authenticated = $this->createGetterCall('isAuthenticated');

            return $node instanceof Isset_
                ? $is_authenticated
                : new Identical($is_authenticated, new ConstFetch(new Name('false')));
        }

        // Other keys getters return a default value when the key is not set, `isset()`/`empty()` checks
        // cannot be converted.
        foreach ($vars as $var) {
            $this->markAsSkipped($var);
        }

        return null;
    }

    /**
     * `$_SESSION['glpilanguage'] ?? 'en_GB'` -> `Session::getLanguage() ?? 'en_GB'`
     * `$_SESSION['glpiID'] ?? false`         -> `Session::getLoginUserID()`
     */
    private function refactorCoalesce(Coalesce $node): ?Node
    {
        if (!($node->left instanceof ArrayDimFetch)) {
            return null;
        }

        $key = $this->getMappedSessionKey($node->left);
        if ($key === null || $node->left->getAttribute(self::SKIP_ATTRIBUTE) === true) {
            return null;
        }

        $default = self::MAPPING[$key]['default'];

        if ($default === null) {
            // The getter returns `null` when the key is not set, the coalesce operator behaves the same way.
            $node->left = $this->createGetterCall(self::MAPPING[$key]['method']);
            return $node;
        }

        if ($this->isLiteralValue($node->right, $default)) {
            // The fallback value is the one returned by the getter when the key is not set.
            return $this->createGetterCall(self::MAPPING[$key]['method']);
        }

        // The getter does not return `null` when the key is not set, the fallback value would never be used.
        $this->markAsSkipped($node->left);

        return null;
    }

    /**
     * Flag the `$_SESSION[...]` fetches of the given target expression as not replaceable.
     *
     * Only the fetched variable chain is flagged, dimensions are still processed,
     * e.g. in `$values[$_SESSION['glpiID']] = 1`, `$_SESSION['glpiID']` is still replaced.
     */
    private function markAsSkipped(Expr $expr): void
    {
        if ($expr instanceof List_ || $expr instanceof Array_) {
            // `[$_SESSION['glpiID'], $other] = $values`
            foreach ($expr->items as $item) {
                if ($item !== null) {
                    $this->markAsSkipped($item->value);
                }
            }
            return;
        }

        while ($expr instanceof ArrayDimFetch) {
            $expr->setAttribute(self::SKIP_ATTRIBUTE, true);
            $expr = $expr->var;
        }
    }

    private function markByRefArgumentsAsSkipped(FuncCall|MethodCall|StaticCall $call): void
    {
        if ($call->isFirstClassCallable()) {
            return;
        }

        $scope = $call->getAttribute(AttributeKey::SCOPE);
        if (!($scope instanceof Scope)) {
            return;
        }

        $reflection = $this->reflection_resolver->resolveFunctionLikeReflectionFromCall($call);
        if ($reflection === null) {
            return;
        }

        $parameters = ParametersAcceptorSelector::selectFromArgs($scope, $call->getArgs(), $reflection->getVariants())
            ->getParameters();

        foreach ($call->getArgs() as $index => $arg) {
            $parameter = null;
            if ($arg->name instanceof Identifier) {
                foreach ($parameters as $candidate) {
                    if ($candidate->getName() === $arg->name->name) {
                        $parameter = $candidate;
                    }
                }
            } else {
                $parameter = $parameters[$index] ?? null;
            }

            if ($parameter !== null && $parameter->passedByReference()->yes()) {
                // e.g. `sort($_SESSION['glpiactiveentities'])` modifies the session.
                $this->markAsSkipped($arg->value);
            }
        }
    }

    private function isInsideSessionClass(Node $node): bool
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        return $scope instanceof Scope
            && \strcasecmp($scope->getClassReflection()?->getName() ?? '', 'Session') === 0;
    }

    /**
     * Returns the session key if the node is a `$_SESSION['key']` fetch of a key that has a getter.
     */
    private function getMappedSessionKey(ArrayDimFetch $node): ?string
    {
        if (
            !($node->var instanceof Variable)
            || $node->var->name !== '_SESSION'
            || !($node->dim instanceof String_)
            || !\array_key_exists($node->dim->value, self::MAPPING)
        ) {
            return null;
        }

        return $node->dim->value;
    }

    /**
     * @param false|0|null|array{} $value
     */
    private function isLiteralValue(Expr $expr, false|int|array|null $value): bool
    {
        return match (true) {
            $value === false => $expr instanceof ConstFetch && $this->isName($expr, 'false'),
            $value === 0     => $expr instanceof Int_ && $expr->value === 0,
            $value === []    => $expr instanceof Array_ && $expr->items === [],
            default          => false,
        };
    }

    private function createGetterCall(string $method): StaticCall
    {
        return new StaticCall(new Name('\\Session'), $method);
    }
}
