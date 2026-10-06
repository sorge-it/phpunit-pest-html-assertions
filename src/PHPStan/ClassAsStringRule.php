<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPStan;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Reports a check that a string holds a class: it passes when the class is anywhere. A
 * negative check is stricter than its selector and is not reported. The rule reads literals of
 * a form that is rare outside a class attribute.
 *
 * @phpstan-type Parameters array{needles: list<array{int, string}>, subject?: array{int, string}, variadic?: true, equality?: true, pattern?: true, negative?: true, result?: string}
 *
 * @implements Rule<CallLike>
 */
final readonly class ClassAsStringRule implements Rule
{
    public const string IDENTIFIER = 'html.classAsString';

    /** What a check of Pest without an argument says about its value. */
    private const array CLAIMS = [
        'tobetrue' => 'true', 'tobetruthy' => 'truthy', 'tobefalse' => 'false', 'tobefalsy' => 'falsy',
        'tobenull' => 'null', 'tobeempty' => 'empty', 'tobeint' => 'int',
    ];

    /** Checks of Pest that compare their value with an expected literal, and the name of that parameter. */
    private const array COMPARISONS = [
        'tobe' => 'expected', 'toequal' => 'expected', 'tobegreaterthan' => 'expected', 'tobegreaterthanorequal' => 'expected',
        'tobelessthan' => 'expected', 'tobelessthanorequal' => 'expected', 'tohavecount' => 'count',
    ];

    /**
     * Assertions of PHPUnit that check a value: the parameter of the value, the claim or the
     * comparison, the parameter of the expected literal, and whether the assertion negates it.
     */
    private const array ASSERTIONS = [
        'asserttrue' => [0, 'condition', 'true'],
        'assertfalse' => [0, 'condition', 'false'],
        'assertnottrue' => [0, 'condition', 'not true'],
        'assertnotfalse' => [0, 'condition', 'not false'],
        'assertnull' => [0, 'actual', 'null'],
        'assertnotnull' => [0, 'actual', 'not null'],
        'assertempty' => [0, 'actual', 'empty'],
        'assertnotempty' => [0, 'actual', 'not empty'],
        'assertsame' => [1, 'actual', 'tobe', 'expected'],
        'assertequals' => [1, 'actual', 'toequal', 'expected'],
        'assertnotsame' => [1, 'actual', 'tobe', 'expected', true],
        'assertnotequals' => [1, 'actual', 'toequal', 'expected', true],
        'assertgreaterthan' => [1, 'actual', 'tobegreaterthan', 'minimum'],
        'assertgreaterthanorequal' => [1, 'actual', 'tobegreaterthanorequal', 'minimum'],
        'assertlessthan' => [1, 'actual', 'tobelessthan', 'maximum'],
        'assertlessthanorequal' => [1, 'actual', 'tobelessthanorequal', 'maximum'],
        'assertcount' => [1, 'haystack', 'tohavecount', 'expectedCount'],
    ];

    /** The claim that a negated check makes. A claim that is not listed says nothing when negated: `not->toBe(2)`. */
    private const array NEGATIONS = [
        'true' => 'not true', 'false' => 'not false', 'truthy' => 'falsy', 'falsy' => 'truthy', 'null' => 'not null',
        'empty' => 'not empty', 'int' => 'not int', 'zero' => 'not zero', 'positive' => 'below one', 'below one' => 'positive',
        'no text' => 'any text', 'count zero' => 'count positive',
        'loose true' => 'not loose true', 'loose false' => 'not loose false', 'loose null' => 'not loose null',
        'loose zero' => 'not loose zero', 'loose empty' => 'not loose empty',
    ];

    /**
     * Whether a claim about the result of a search means that it found its text: by the kind of
     * the result. A claim that is not listed says neither: `toBeTruthy()` on a position, which is
     * 0 at the start of the string, or `toEqual(0)`, which is also true for `false`.
     */
    private const array FOUND = [
        'bool' => [
            'true' => true, 'truthy' => true, 'not false' => true, 'not empty' => true, 'loose true' => true, 'not loose false' => true,
            'not loose null' => true, 'not loose zero' => true, 'not loose empty' => true,
            'false' => false, 'falsy' => false, 'not true' => false, 'empty' => false, 'not loose true' => false, 'loose false' => false,
            'loose null' => false, 'loose zero' => false, 'loose empty' => false,
        ],
        'position' => [
            'int' => true, 'zero' => true, 'number' => true, 'positive' => true, 'not false' => true, 'not loose false' => true,
            'not loose null' => true, 'not loose zero' => true, 'not loose empty' => true,
            'false' => false, 'not int' => false, 'loose empty' => false,
        ],
        'count' => [
            'number' => true, 'positive' => true, 'truthy' => true, 'not zero' => true, 'not empty' => true, 'loose true' => true,
            'not loose false' => true, 'not loose null' => true, 'not loose zero' => true,
            'zero' => false, 'below one' => false, 'falsy' => false, 'empty' => false, 'not loose true' => false, 'loose false' => false,
            'loose null' => false, 'loose zero' => false,
        ],
        'string' => [
            'truthy' => true, 'not empty' => true, 'text' => true, 'any text' => true, 'loose true' => true, 'not loose false' => true,
            'not loose null' => true, 'not loose empty' => true,
            'falsy' => false, 'empty' => false, 'no text' => false, 'not loose true' => false, 'loose false' => false, 'loose null' => false,
            'loose empty' => false,
        ],
        'collection' => ['not empty' => true, 'count positive' => true, 'empty' => false, 'count zero' => false],
    ];

    /** Methods of a Pest expectation that keep the value they check. */
    private const array KEEPS_VALUE = ['when', 'unless'];

    /** Stands for a variable in a literal. It completes only a utility that has a form of Tailwind already. */
    private const string PLACEHOLDER = 'x';

    /** A variant of Tailwind: a known name, a name that a group of variants starts with, or an arbitrary variant. */
    private const string VARIANT = '(?:sm|md|lg|xl|2xl|hover|focus|focus-within|focus-visible|active|visited|target|disabled|enabled'
        .'|checked|indeterminate|default|required|valid|invalid|in-range|out-of-range|placeholder-shown|autofill|read-only|open'
        .'|empty|first|last|only|odd|even|first-of-type|last-of-type|only-of-type|before|after|placeholder|file|marker|selection'
        .'|first-line|first-letter|backdrop|dark|print|portrait|landscape|ltr|rtl|motion-safe|motion-reduce|contrast-more'
        .'|contrast-less|forced-colors|starting|inert|user-valid|user-invalid|noscript'
        .'|(?:max|min)-(?:sm|md|lg|xl|2xl|\[[^\]\s]+\])'
        .'|(?:group|peer)-(?:[a-z][a-z-]*|\[[^\]\s]+\])(?:/[a-z0-9_-]+)?'
        .'|(?:aria|data|has|not|in|supports)-(?:[a-z][a-z-]*|\[[^\]\s]+\])'
        .'|nth(?:-last)?(?:-of-type)?-(?:\d+|\[[^\]\s]+\])'
        .'|@(?:max-|min-)?(?:3xs|2xs|xs|sm|md|lg|xl|[2-7]xl|\[[^\]\s]+\])(?:/[a-z0-9_-]+)?'
        .'|\*\*?|\[[&@][^\]\s]*\])';

    /** A utility of Tailwind after its variants, with `!`, `-` and `/`, or an arbitrary property. */
    private const string VARIANT_TOKEN = '~^(?:'.self::VARIANT.':)+!?-?(?<utility>[a-z][a-z0-9-]*(?:-\[(?![A-Za-z]+\])[^\]\s]+\])?(?:/[a-z0-9.\[\]]+)?|\[[a-z][a-z-]*:[^\]\s]+\])!?$~';

    /** A utility of Tailwind with an arbitrary value in `[ ]`. A word alone in it is a key: `errors-[name]`. */
    private const string BRACKET_TOKEN = '~^!?-?[a-z][a-z0-9-]*-\[(?![A-Za-z]+\])[^\]\s]+\](?:/[a-z0-9.\[\]]+)?!?$~';

    /** A class in camelCase. Outside of a project that names its classes so, it is mostly a name of JS or PHP. */
    private const string CAMEL_CASE_TOKEN = '~^[a-z][a-z0-9]*[A-Z][a-zA-Z0-9]*$~';

    /** A token that can stand in a class attribute but also in text: `flex`, `header-grid`. */
    private const string PLAIN_TOKEN = '~^!?-?[a-z][a-z0-9-]*!?$~';

    /**
     * Utilities of one word. After a variant, a word that is not one of them is text:
     * `after:today` is a rule of validation, `first:name` a key.
     */
    private const array ONE_WORD_UTILITIES = [
        'flex', 'grid', 'block', 'inline', 'hidden', 'contents', 'table', 'container', 'static', 'fixed', 'absolute',
        'relative', 'sticky', 'visible', 'invisible', 'collapse', 'underline', 'overline', 'italic', 'uppercase',
        'lowercase', 'capitalize', 'truncate', 'border', 'rounded', 'shadow', 'outline', 'ring', 'blur', 'grow',
        'shrink', 'transition', 'transform', 'isolate', 'antialiased', 'invert', 'grayscale', 'sepia', 'filter',
        'ordinal', 'resize', 'prose', 'contain', 'columns',
    ];

    public function __construct(private bool $camelCaseClasses = false) {}

    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /** @return list<IdentifierRuleError> */
    public function processNode(Node $node, Scope $scope): array
    {
        $name = StringCalls::nameOf($node);

        if (preg_match('~[/\\\\]tests[/\\\\]~i', $scope->getFile()) !== 1 || $node->isFirstClassCallable() || $name === null) {
            return [];
        }

        $method = mb_strtolower($name);

        // The line of the call, not the line of `expect()`: a chain holds many checks.
        return array_values(array_filter([
            $this->checksString($node, $method, $scope) ? $this->error($name, $this->lineOf($node)) : null,
            $this->checkedRead($node, $method, $scope),
        ]));
    }

    private function error(string $name, int $line): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf('%s() searches a string for a class: ask the element with toHaveSelectorClass() or assertHtmlSelectorClass().', $name))
            ->identifier(self::IDENTIFIER)
            ->line($line)
            ->tip('When the string is not HTML, add // @phpstan-ignore html.classAsString (<reason>).')
            ->build();
    }

    private function lineOf(CallLike $node): int
    {
        return ($node instanceof MethodCall || $node instanceof NullsafeMethodCall || $node instanceof StaticCall) ? $node->name->getStartLine() : $node->getStartLine();
    }

    /** A positive check of Pest or PHPUnit that a string holds a class. */
    private function checksString(CallLike $node, string $method, Scope $scope): bool
    {
        /** @var Parameters|null $parameters */
        $parameters = StringCalls::STRING_CHECKS[$method] ?? null;

        if ($parameters === null || isset($parameters['equality']) || isset($parameters['negative'])) {
            return false;
        }

        $arguments = array_values($node->getArgs());

        if (str_starts_with($method, 'assert')) {
            $subject = isset($parameters['subject']) ? StringCalls::argument($arguments, ...$parameters['subject'])?->value : null;

            return $subject instanceof Expr && $this->isHtml($subject, $scope->getType($subject))
                && $this->holdsClasses($this->needles($arguments, $parameters), $parameters);
        }

        $value = $node instanceof MethodCall && ! $this->isProperty($node->var, 'not') ? $this->expectedValue($node) : null;

        return $value !== null && $this->isHtml($value['each'] ? null : $value['expr'], $this->typeOfValue($value, $scope))
            && $this->holdsClasses($this->needles($arguments, $parameters), $parameters);
    }

    /**
     * A search (`str_contains`, `Str::contains`, `preg_match`) whose result a check says is
     * found. A search outside a check is not read, and a cut (`Str::after`) never: its result
     * does not tell whether it found its text.
     */
    private function checkedRead(CallLike $node, string $method, Scope $scope): ?IdentifierRuleError
    {
        [$result, $claim] = $this->resultOf($node, $method) ?? [null, null];
        $negated = $result instanceof BooleanNot;
        $result = $result instanceof BooleanNot ? $result->expr : $result;

        $readName = $result instanceof CallLike ? StringCalls::nameOf($result) : null;
        $read = $result instanceof CallLike && $readName !== null ? $this->readOf($result, mb_strtolower($readName), $scope) : null;
        $kind = $read['parameters']['result'] ?? null;

        // `!` turns only a bool into its opposite: `! strpos(…)` is also true at position 0.
        $claim = $negated ? ($kind === 'bool' && $claim !== null ? self::NEGATIONS[$claim] ?? null : null) : $claim;
        $found = $kind !== null && $claim !== null ? self::FOUND[$kind][$claim] ?? null : null;

        if ($read === null || $found === null || $found === isset($read['parameters']['negative'])) {
            return null;
        }

        return $this->isHtml($read['subject'], $read['type'])
            && $this->holdsClasses($this->needles($read['arguments'], $read['parameters']), $read['parameters'])
            ? $this->error($readName, $this->lineOf($result))
            : null;
    }

    /**
     * The value a check reads and what the check claims about it, as the keys of FOUND. Null
     * when the call is no such check or makes no claim.
     *
     * @return array{Expr, string}|null
     */
    private function resultOf(CallLike $node, string $method): ?array
    {
        $arguments = array_values($node->getArgs());

        if (isset(self::ASSERTIONS[$method])) {
            $assertion = self::ASSERTIONS[$method];
            $value = StringCalls::argument($arguments, $assertion[0], $assertion[1])?->value;
            $claim = isset($assertion[3])
                ? $this->compared($assertion[2], StringCalls::argument($arguments, 0, $assertion[3])?->value)
                : $assertion[2];
            $claim = isset($assertion[4]) && $claim !== null ? self::NEGATIONS[$claim] ?? null : $claim;

            return $value instanceof Expr && $claim !== null ? [$value, $claim] : null;
        }

        if (! $node instanceof MethodCall) {
            return null;
        }

        $negated = $this->isProperty($node->var, 'not');
        $start = $negated && $node->var instanceof PropertyFetch ? $node->var->var : $node->var;
        $value = $this->startsChain($start) && $start instanceof CallLike ? ($start->getArgs()[0] ?? null)?->value : null;

        $claim = self::CLAIMS[$method] ?? (isset(self::COMPARISONS[$method])
            ? $this->compared($method, StringCalls::argument($arguments, 0, self::COMPARISONS[$method])?->value)
            : null);
        $claim = $negated && $claim !== null ? self::NEGATIONS[$claim] ?? null : $claim;

        return $value instanceof Expr && $claim !== null ? [$value, $claim] : null;
    }

    /** What a comparison with an expected literal claims: `toBe(0)` claims zero, `toBeGreaterThan(0)` a positive number. */
    private function compared(string $comparison, ?Expr $expected): ?string
    {
        $number = $expected instanceof Int_ ? $expected->value : null;
        $constant = $expected instanceof ConstFetch ? $expected->name->toLowerString() : null;
        // `toEqual` compares loosely: `false == 0`, `false == ''` and `null == 0` are true.
        $loose = $comparison === 'toequal' ? 'loose ' : '';

        return match (true) {
            $comparison === 'tobe' || $comparison === 'toequal' => match (true) {
                in_array($constant, ['true', 'false', 'null'], true) => $loose.$constant,
                $number !== null => $number === 0 ? $loose.'zero' : 'number',
                $expected instanceof String_ && $expected->value === '' => $loose === '' ? 'no text' : 'loose empty',
                $expected instanceof String_ => 'text',
                default => null,
            },
            $number === null => null,
            $comparison === 'tohavecount' => $number === 0 ? 'count zero' : 'count positive',
            $comparison === 'tobegreaterthan' => 'positive',
            $comparison === 'tobegreaterthanorequal' => $number >= 1 ? 'positive' : null,
            $comparison === 'tobelessthan' => $number === 1 ? 'below one' : null,
            $comparison === 'tobelessthanorequal' => $number === 0 ? 'below one' : null,
            default => null,
        };
    }

    /**
     * A search of PHP, of `Str` or of Laravel's `Stringable`: its parameters, its arguments and
     * the string it searches. A collection is `Stringable` too, by its `__toString()`, so only
     * the class of Laravel counts.
     *
     * @return array{parameters: Parameters, arguments: list<Arg>, subject: Expr, type: Type}|null
     */
    private function readOf(CallLike $read, string $method, Scope $scope): ?array
    {
        $arguments = array_values($read->getArgs());
        $object = $read instanceof MethodCall || $read instanceof NullsafeMethodCall;

        /** @var Parameters|null $parameters */
        $parameters = match (true) {
            $read instanceof FuncCall => StringCalls::FUNCTIONS[$method] ?? null,
            $read instanceof StaticCall => $read->class instanceof Name && $scope->resolveName($read->class) === Str::class ? StringCalls::STRING_READS[$method] ?? null : null,
            $object => (new ObjectType(Stringable::class))->isSuperTypeOf(TypeCombinator::removeNull($scope->getType($read->var)))->yes()
                ? $this->withoutSubject(StringCalls::STRING_READS[$method] ?? null)
                : null,
            default => null,
        };

        $subject = match (true) {
            $parameters === null => null,
            $object => $read->var,
            default => isset($parameters['subject']) ? StringCalls::argument($arguments, ...$parameters['subject'])?->value : null,
        };

        return $parameters !== null && $subject instanceof Expr
            ? ['parameters' => $parameters, 'arguments' => $arguments, 'subject' => $subject, 'type' => $object ? new StringType : $scope->getType($subject)]
            : null;
    }

    /**
     * The parameters of a method of `Stringable`: the object is the subject, so each later
     * parameter moves one place forward.
     *
     * @param  Parameters|null  $parameters
     * @return Parameters|null
     */
    private function withoutSubject(?array $parameters): ?array
    {
        if ($parameters === null || ! isset($parameters['subject'])) {
            return $parameters;
        }

        $subject = $parameters['subject'][0];
        $parameters['needles'] = array_map(fn (array $needle): array => [$needle[0] > $subject ? $needle[0] - 1 : $needle[0], $needle[1]], $parameters['needles']);
        unset($parameters['subject']);

        // `Stringable::match()` returns a `Stringable`, and an object is always truthy.
        if (($parameters['result'] ?? null) === 'string') {
            unset($parameters['result']);
        }

        return $parameters;
    }

    /**
     * The value a check of Pest reads: the argument of `expect()` or `and()`, and whether the
     * check reads one element of it after `each`. Null when the chain changes the value.
     *
     * @return array{expr: Expr, each: bool}|null
     */
    private function expectedValue(MethodCall $check): ?array
    {
        $each = false;
        $link = $check->var;

        while (! $this->startsChain($link)) {
            $each = $each || $this->isProperty($link, 'each');
            $link = $this->previousLink($link);

            if (! $link instanceof Expr) {
                return null;
            }
        }

        $value = $link instanceof CallLike ? ($link->getArgs()[0] ?? null)?->value : null;

        return $value instanceof Expr ? ['expr' => $value, 'each' => $each] : null;
    }

    /** @param array{expr: Expr, each: bool} $value */
    private function typeOfValue(array $value, Scope $scope): Type
    {
        $type = $scope->getType($value['expr']);

        return $value['each'] ? $type->getIterableValueType() : $type;
    }

    /** `expect($value)` or `->and($value)`. */
    private function startsChain(Expr $link): bool
    {
        return ($link instanceof FuncCall && $link->name instanceof Name && mb_strtolower($link->name->getLast()) === 'expect')
            || ($link instanceof MethodCall && $link->name instanceof Identifier && $link->name->toLowerString() === 'and');
    }

    /** The link before `->not`, `->each`, another check or a method that keeps the value; null after any other link. */
    private function previousLink(Expr $link): ?Expr
    {
        return match (true) {
            $link instanceof PropertyFetch => $this->isProperty($link, 'not') || $this->isProperty($link, 'each') ? $link->var : null,
            $link instanceof MethodCall && $link->name instanceof Identifier => str_starts_with($link->name->toLowerString(), 'to') || in_array($link->name->toLowerString(), self::KEEPS_VALUE, true) ? $link->var : null,
            default => null,
        };
    }

    private function isProperty(Expr $link, string $name): bool
    {
        return $link instanceof PropertyFetch && $link->name instanceof Identifier && $link->name->toLowerString() === $name;
    }

    /**
     * A string, also `string|false` from `getContent()` and `?string`, that is not the value of a
     * class attribute: `$element->getAttribute('class')` holds classes and no HTML.
     */
    private function isHtml(?Expr $subject, Type $type): bool
    {
        $readsClassAttribute = $subject instanceof CallLike && ! $subject->isFirstClassCallable()
            && array_any($subject->getArgs(), fn (Arg $argument): bool => $argument->value instanceof String_ && mb_strtolower($argument->value->value) === 'class');

        return ! $readsClassAttribute && TypeCombinator::removeNull(TypeCombinator::remove($type, new ConstantBooleanType(false)))->isString()->yes();
    }

    /**
     * The arguments a call searches for: every argument of a variadic check, else each named parameter.
     *
     * @param  list<Arg>  $arguments
     * @param  Parameters  $parameters
     * @return list<Expr>
     */
    private function needles(array $arguments, array $parameters): array
    {
        if (isset($parameters['variadic'])) {
            return array_map(fn (Arg $argument): Expr => $argument->value, array_values(array_filter($arguments, fn (Arg $argument): bool => ! $argument->name instanceof Identifier)));
        }

        return array_values(array_filter(array_map(
            fn (array $needle): ?Expr => StringCalls::argument($arguments, ...$needle)?->value,
            $parameters['needles'],
        )));
    }

    /**
     * @param  list<Expr>  $needles
     * @param  Parameters  $parameters
     */
    private function holdsClasses(array $needles, array $parameters): bool
    {
        return array_any($needles, fn (Expr $needle): bool => array_any(
            $this->texts($needle),
            fn (string $text): bool => $this->namesClasses(isset($parameters['pattern']) ? $this->patternBody($text) : $text),
        ));
    }

    /**
     * The texts an argument searches for: a string, the values of an array, and an interpolated
     * string or a concatenation with a placeholder for each variable. Not the keys of an array.
     *
     * @return list<string>
     */
    private function texts(Expr $needle): array
    {
        if ($needle instanceof Array_) {
            return array_merge([], ...array_map(fn (?ArrayItem $item): array => $item instanceof ArrayItem ? $this->texts($item->value) : [], $needle->items));
        }

        $text = $this->joined($needle);

        return $text === null ? [] : [$text];
    }

    /** The text of an expression with a placeholder for each variable, or null when no literal is in it. */
    private function joined(Expr $part): ?string
    {
        return match (true) {
            $part instanceof String_ => $part->value,
            $part instanceof InterpolatedString => array_any($part->parts, fn (Node $piece): bool => $piece instanceof InterpolatedStringPart)
                ? implode('', array_map(fn (Node $piece): string => $piece instanceof InterpolatedStringPart ? $piece->value : self::PLACEHOLDER, $part->parts))
                : null,
            $part instanceof Concat => $this->joined($part->left) === null && $this->joined($part->right) === null
                ? null
                : ($this->joined($part->left) ?? self::PLACEHOLDER).($this->joined($part->right) ?? self::PLACEHOLDER),
            default => null,
        };
    }

    /** The body of a regular expression without its delimiters, flags and the escapes of punctuation; null when it is more than a literal. */
    private function patternBody(string $pattern): ?string
    {
        $open = mb_substr($pattern, 0, 1);
        $close = ['(' => ')', '{' => '}', '[' => ']', '<' => '>'][$open] ?? $open;
        $end = mb_strrpos($pattern, $close);
        $body = preg_match('~^[^\w\s\\\\]$~', $open) === 1 && $end !== false && $end > 0 ? mb_substr($pattern, 1, $end - 1) : $pattern;

        // A character class, a group, an alternative, a quantifier or an anchor that is not escaped.
        return preg_match('~(?<!\\\\)[\[\]()|?+*{}^$]~', $body) === 1 ? null : preg_replace('~\\\\([^\w\s])~', '$1', $body) ?? $body;
    }

    /**
     * Every token can stand in a class attribute, and one of them has a form that text does not
     * have. A literal with markup is left to `html.markupAsString`.
     */
    private function namesClasses(?string $text): bool
    {
        $tokens = ($text === null ? [] : preg_split('~\s+~', $text, flags: PREG_SPLIT_NO_EMPTY)) ?: [];

        return $tokens !== [] && preg_match('~'.MarkupAsStringRule::MARKUP.'~i', $text) !== 1
            && array_all($tokens, fn (string $token): bool => $this->isTailwind($token) || preg_match(self::CAMEL_CASE_TOKEN, $token) === 1 || preg_match(self::PLAIN_TOKEN, $token) === 1)
            && array_any($tokens, fn (string $token): bool => $this->isTailwind($token) || ($this->camelCaseClasses && preg_match(self::CAMEL_CASE_TOKEN, $token) === 1));
    }

    /** A utility with `[ ]`, or a utility after a variant that has a digit, a `-`, a `/`, `[ ]`, or is a known word. */
    private function isTailwind(string $token): bool
    {
        if (preg_match(self::BRACKET_TOKEN, $token) === 1) {
            return true;
        }

        return preg_match(self::VARIANT_TOKEN, $token, $match) === 1
            && (preg_match('~[\d\[/-]~', $match['utility']) === 1 || in_array($match['utility'], self::ONE_WORD_UTILITIES, true));
    }
}
