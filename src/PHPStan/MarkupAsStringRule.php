<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPStan;

use Illuminate\Support\Str;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Reports a test that checks rendered HTML as a string. PHPStan cannot know
 * whether a string is HTML, so the rule reads literals: a call that only
 * exists for markup, or a literal that holds a tag or an attribute.
 *
 * @implements Rule<CallLike>
 */
final class MarkupAsStringRule implements Rule
{
    public const string IDENTIFIER = 'html.markupAsString';

    /** Laravel and Livewire checks that compare markup as a string, whatever they are given. */
    private const array MARKUP_CHECKS = ['assertseehtml', 'assertdontseehtml', 'assertseehtmlinorder', 'assertseeinorder'];

    /** Laravel checks that compare markup where their second argument turns escaping off. */
    private const array UNESCAPED_CHECKS = ['assertsee', 'assertdontsee'];

    /** Checks of Pest and PHPUnit that compare or search a string. */
    private const array STRING_CHECKS = [
        'tocontain', 'tobe', 'tostartwith', 'toendwith', 'tomatch',
        'assertstringcontainsstring', 'assertstringnotcontainsstring', 'assertstringstartswith', 'assertstringendswith',
        'assertmatchesregularexpression', 'assertdoesnotmatchregularexpression',
    ];

    /** Methods of `Str` and of its `Stringable` that cut or search a string. */
    private const array STRING_READS = [
        'after', 'afterlast', 'before', 'beforelast', 'between', 'betweenfirst',
        'contains', 'containsall', 'startswith', 'endswith', 'substrcount', 'match', 'matchall', 'test',
    ];

    /** Functions of PHP that search a string. */
    private const array FUNCTIONS = [
        'preg_match', 'preg_match_all', 'str_contains', 'str_starts_with', 'str_ends_with',
        'strpos', 'stripos', 'mb_strpos', 'mb_stripos', 'substr_count', 'mb_substr_count',
    ];

    /**
     * An opening or closing tag (also in a regular expression), an attribute of
     * HTML with its value, or the name of a `data-*` or `wire:` attribute. After
     * `[` it is a CSS selector, not markup.
     */
    private const string MARKUP = '</?[a-z][a-z0-9-]*(?=[\s/>\[]|$)'
        .'|(?<![\w\[-])(?:class|id|href|src|srcset|alt|title|name|value|type|role|style|for|rel|target|lang|aria-[a-z-]+|data-[a-z0-9-]+|wire:[a-z.:-]+|x-[a-z.:-]+)="'
        .'|(?<![\w/.:\[-])data-[a-z][a-z0-9-]*|(?<![\w/.\[-])wire:[a-z]';

    /** A cut or a search also by the bracket of a tag alone: `Str::betweenFirst($html, 'data-x', '>')`. */
    private const string READ_MARKUP = self::MARKUP.'|^[<>]$';

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        if (preg_match('~[/\\\\]tests[/\\\\]~i', $scope->getFile()) !== 1 || $node->isFirstClassCallable()) {
            return [];
        }

        $message = $this->findMessage($node, $scope);

        return $message === null ? [] : [
            RuleErrorBuilder::message($message)
                ->identifier(self::IDENTIFIER)
                ->tip('Ask the DOM with a CSS selector, see the README of sorge-it/phpunit-pest-html-assertions.')
                ->build(),
        ];
    }

    private function findMessage(CallLike $node, Scope $scope): ?string
    {
        $name = $this->nameOf($node);

        if ($name === null) {
            return null;
        }

        $method = mb_strtolower($name);
        $arguments = $node->getArgs();

        return match (true) {
            $node instanceof FuncCall => in_array($method, self::FUNCTIONS, true) && $this->holdsMarkup($arguments, self::MARKUP)
                ? sprintf('%s() reads HTML as a string: its literal holds a tag or an attribute.', $name)
                : null,
            in_array($method, self::MARKUP_CHECKS, true) => sprintf('%s() checks markup as a string.', $name),
            in_array($method, self::UNESCAPED_CHECKS, true) && $this->turnsEscapingOff($arguments) => sprintf('%s() with escaping off checks markup as a string.', $name),
            in_array($method, self::STRING_CHECKS, true) && $this->holdsMarkup($arguments, self::MARKUP) => sprintf('%s() checks markup as a string: its literal holds a tag or an attribute.', $name),
            in_array($method, self::STRING_READS, true) && $this->readsAString($node, $scope) && $this->holdsMarkup($arguments, self::READ_MARKUP) => sprintf('%s() reads HTML as a string: its literal holds a tag or an attribute.', $name),
            default => null,
        };
    }

    private function nameOf(CallLike $node): ?string
    {
        return match (true) {
            ($node instanceof MethodCall || $node instanceof NullsafeMethodCall || $node instanceof StaticCall) && $node->name instanceof Identifier => $node->name->toString(),
            $node instanceof FuncCall && $node->name instanceof Name => $node->name->toString(),
            default => null,
        };
    }

    /** A method of `Str` itself, or of the `Stringable` it hands out. */
    private function readsAString(CallLike $node, Scope $scope): bool
    {
        return match (true) {
            $node instanceof StaticCall => $node->class instanceof Name && $scope->resolveName($node->class) === Str::class,
            $node instanceof MethodCall, $node instanceof NullsafeMethodCall => true,
            default => false,
        };
    }

    /** @param array<Arg> $arguments */
    private function turnsEscapingOff(array $arguments): bool
    {
        $escape = $arguments[1] ?? $this->named($arguments, 'escape');

        return $escape instanceof Arg && $escape->value instanceof ConstFetch && $escape->value->name->toLowerString() === 'false';
    }

    /** @param array<Arg> $arguments */
    private function named(array $arguments, string $name): ?Arg
    {
        return array_find($arguments, fn (Arg $argument): bool => $argument->name?->toString() === $name);
    }

    /** @param array<Arg> $arguments */
    private function holdsMarkup(array $arguments, string $markup): bool
    {
        $finder = new NodeFinder;

        foreach ($arguments as $argument) {
            foreach ($finder->find($argument->value, fn (Node $part): bool => $part instanceof String_ || $part instanceof InterpolatedStringPart) as $literal) {
                if (($literal instanceof String_ || $literal instanceof InterpolatedStringPart) && preg_match('~'.$markup.'~i', $literal->value) === 1) {
                    return true;
                }
            }
        }

        return false;
    }
}
