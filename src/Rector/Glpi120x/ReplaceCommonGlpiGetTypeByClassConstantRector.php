<?php

declare(strict_types=1);

namespace RectorGlpi\Rector\Glpi120x;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PHPStan\Type\ObjectType;
use PhpParser\Node\Expr\StaticCall;

final class ReplaceCommonGlpiGetTypeByClassConstantRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace `CommonGLPI::getType()` calls by `::class` constant',
            [
                new CodeSample(
                    '$itemtype = $item->getType();',
                    '$itemtype = $item::class;'
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (
            ($node instanceof MethodCall) === false
            && ($node instanceof StaticCall) === false
        ) {
            // It should not happen
            return null;
        }

        if ($this->isName($node->name, 'getType') === false) {
            // Process only `getType()` method
            return null;
        }

        $target = match (true) {
            $node instanceof MethodCall => $node->var,
            $node instanceof StaticCall => $node->class,
        };

        if ($this->isObjectType($target, new ObjectType('CommonGLPI')) === false) {
            // Process only `CommonGLPI::getType()`
            return null;
        }

        if ($this->isBetweeCurlyBracesInsideStringInterpolation($node)) {
            // Occurences inside string interpolations (e.g. `"Type: {$item->getType()}"`) cannot be replaced
            // since it would produce a syntax error:
            // `PHP Parse error:  syntax error, unexpected token "}", expecting "->" or "?->" or "["`
            return null;
        }

        if (
            ($target instanceof Variable && $target->name === 'this')
            || ($target instanceof Name && $target->name === 'self')
        ) {
            $target = new Name('static');
        }

        return new ClassConstFetch($target, 'class');
    }

    private function isBetweeCurlyBracesInsideStringInterpolation(Node $node): bool
    {
        $tokens = $this->file->getOldTokens();
        $previous_token_pos = $node->getStartTokenPos() - 1;
        if (
            \array_key_exists($previous_token_pos, $tokens)
            && $tokens[$previous_token_pos]->id === T_CURLY_OPEN
        ) {
            return true;
        }

        return false;
    }
}
