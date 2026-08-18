<?php

declare(strict_types=1);

namespace RectorGlpi\Rector\Glpi120x;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PHPStan\Type\ObjectType;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Arg;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Expr\StaticPropertyFetch;

final class ReplaceHardcodedRightnameByCommonDBTMRightnamePropertyRector extends AbstractRector
{
    /**
     * Mapping of values that can be automatically detected because:
     *  - classname is namespaced;
     *  - rightname does not match the classname hosting it.
     *
     * @var array<string, string>
     */
    private const MAPPING = [
        'dashboard'                 => 'Glpi\\Dashboard\\Dashboard',
        'followup'                  => 'ITILFollowup',
        'knowbase'                  => 'KnowbaseItem',
        'license'                   => 'SoftwareLicense',
        'logs'                      => 'Log',
        'networking'                => 'NetworkPort',
        'reminder_public'           => 'Reminder',
        'reports'                   => 'Report',
        'rssfeed_public'            => 'RSSFeed',
        'rule_change'               => 'RuleChange',
        'rule_dictionnary_dropdown' => 'RuleDictionnaryDropdown',
        'rule_dictionnary_printer'  => 'RuleDictionnaryPrinter',
        'rule_dictionnary_software' => 'RuleDictionnarySoftware',
        'rule_ldap'                 => 'RuleRight',
        'rule_location'             => 'RuleLocation',
        'rule_mailcollector'        => 'RuleMailCollector',
        'rule_softwarecategories'   => 'RuleSoftwareCategory',
        'rule_ticket'               => 'RuleTicket',
        'search_config'             => 'DisplayPreference',
        'statistic'                 => 'Stat',
    ];

    /**
     * Rightnames that may refere to multiple classes.
     * We cannot automatically guess what it the correct class to target.
     *
     * @var list<string>
     */
    private const AMBIGUOUS_RIGHTNAMES = [
        'contact_enterprise',
        'dropdown',
        'device',
        'internet',
        'rule_import',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace hardcoded rightname by `CommonDBTM::$rightname` property',
            [
                new CodeSample(
                    'Session::haveRight(\'computer\', CREATE)',
                    'Session::haveRight(Computer::$rightname, CREATE)'
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        // filtering $node to keep only Session::checkRight or Session::checkRightsOr, etc
        if (($node instanceof StaticCall) === false) {
            // It should not happen.
            return null;
        }

        if ($this->isObjectType($node->class, new ObjectType('Session')) === false) {
            // Process only `Session` methods.
            return null;
        }

        if ($this->isNames($node->name, ['checkRight', 'checkRightsOr', 'haveRight', 'haveRightsAnd', 'haveRightsOr']) === false) {
            // Process only given methods.
            return null;
        }

        // Extract the `$module` argument.
        $rightname_arg = null;
        foreach ($node->args as $index => $arg) {
            if (!($arg instanceof Arg)) {
                continue;
            }
            if (
                ($index === 0 && $arg->name === null)
                || ($arg->name instanceof Identifier && $arg->name->name === 'module')
            ) {
                $rightname_arg = $arg;
            }
        }

        if ($rightname_arg === null || !($rightname_arg->value instanceof String_)) {
            return null;
        }

        // Expected node (Session::checkXXX) with hardcoded string matched.
        $hardcoded_value = $rightname_arg->value->value;

        // Ignore ambiguous values.
        if (\in_array($hardcoded_value, self::AMBIGUOUS_RIGHTNAMES, true)) {
            return null;
        }

        // @TODO Classnames using multiple uppercase letters in their name will not be found automatically.
        // A service using a logic similar to `DbUtils::fixItemtypeCase()` could be implemented to find the correct case,
        // by scanning the GLPI/plugin `src` directories.
        $expected_class  = \ucfirst($hardcoded_value);

        // Refer on curated mapping.
        if (\array_key_exists($hardcoded_value, self::MAPPING)) {
            // Hardcoded value matches a mapped value,
            // e.g. `"networking"` -> `NetworkPort::$rightname
            $rightname_arg->value = new StaticPropertyFetch(new Name('\\' . self::MAPPING[$hardcoded_value]), 'rightname');
            return $node;
        }

        // Guess the classname from the hardcoded value.
        if (\is_a($expected_class, 'CommonGLPI', true) && $expected_class::$rightname === $hardcoded_value) {
            // The guessed classname corresponds to an existing `CommonGLPI` class that declares the
            // hardcoded value as its own rightname, e.g. `"computer"` -> `Computer::$rightname`.
            // Comparing the rightname discards coincidental classname matches: `"rack"` guesses
            // `Rack`, but `Rack::$rightname` is `datacenter`, so the value is left untouched.
            $rightname_arg->value = new StaticPropertyFetch(new Name('\\' . $expected_class), 'rightname');
            return $node;
        }

        return null;
    }
}
