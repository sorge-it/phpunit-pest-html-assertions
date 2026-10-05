<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPStan;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

/**
 * The calls that compare, search or cut a string. Both rules read them from here. Each call names
 * its parameters by position and name: `needles` is the text it searches for, `subject` the
 * string it searches. A call of `Stringable` has no subject: it is the object. A search names
 * the kind of its `result`: a `bool`, a `position` or `false`, a `count`, the matched `string`,
 * a `collection` of matches, or the `cut` part of the string.
 */
final class StringCalls
{
    /** Checks of Pest and PHPUnit that compare or search a string. Pest takes its subject from `expect()`. */
    public const array STRING_CHECKS = [
        'tocontain' => ['needles' => [[0, 'needles']], 'variadic' => true],
        'tobe' => ['needles' => [[0, 'expected']], 'equality' => true],
        'tostartwith' => ['needles' => [[0, 'expected']]],
        'toendwith' => ['needles' => [[0, 'expected']]],
        'tomatch' => ['needles' => [[0, 'expression']], 'pattern' => true],
        'assertstringcontainsstring' => ['needles' => [[0, 'needle']], 'subject' => [1, 'haystack']],
        'assertstringcontainsstringignoringcase' => ['needles' => [[0, 'needle']], 'subject' => [1, 'haystack']],
        'assertstringcontainsstringignoringlineendings' => ['needles' => [[0, 'needle']], 'subject' => [1, 'haystack']],
        'assertstringnotcontainsstring' => ['needles' => [[0, 'needle']], 'subject' => [1, 'haystack'], 'negative' => true],
        'assertstringnotcontainsstringignoringcase' => ['needles' => [[0, 'needle']], 'subject' => [1, 'haystack'], 'negative' => true],
        'assertstringstartswith' => ['needles' => [[0, 'prefix']], 'subject' => [1, 'string']],
        'assertstringstartsnotwith' => ['needles' => [[0, 'prefix']], 'subject' => [1, 'string'], 'negative' => true],
        'assertstringendswith' => ['needles' => [[0, 'suffix']], 'subject' => [1, 'string']],
        'assertstringendsnotwith' => ['needles' => [[0, 'suffix']], 'subject' => [1, 'string'], 'negative' => true],
        'assertmatchesregularexpression' => ['needles' => [[0, 'pattern']], 'subject' => [1, 'string'], 'pattern' => true],
        'assertdoesnotmatchregularexpression' => ['needles' => [[0, 'pattern']], 'subject' => [1, 'string'], 'pattern' => true, 'negative' => true],
    ];

    /** Methods of `Str` and of its `Stringable` that cut or search a string. */
    public const array STRING_READS = [
        'after' => ['needles' => [[1, 'search']], 'subject' => [0, 'subject'], 'result' => 'cut'],
        'afterlast' => ['needles' => [[1, 'search']], 'subject' => [0, 'subject'], 'result' => 'cut'],
        'before' => ['needles' => [[1, 'search']], 'subject' => [0, 'subject'], 'result' => 'cut'],
        'beforelast' => ['needles' => [[1, 'search']], 'subject' => [0, 'subject'], 'result' => 'cut'],
        'between' => ['needles' => [[1, 'from'], [2, 'to']], 'subject' => [0, 'subject'], 'result' => 'cut'],
        'betweenfirst' => ['needles' => [[1, 'from'], [2, 'to']], 'subject' => [0, 'subject'], 'result' => 'cut'],
        'contains' => ['needles' => [[1, 'needles']], 'subject' => [0, 'haystack'], 'result' => 'bool'],
        'containsall' => ['needles' => [[1, 'needles']], 'subject' => [0, 'haystack'], 'result' => 'bool'],
        'doesntcontain' => ['needles' => [[1, 'needles']], 'subject' => [0, 'haystack'], 'negative' => true, 'result' => 'bool'],
        'startswith' => ['needles' => [[1, 'needles']], 'subject' => [0, 'haystack'], 'result' => 'bool'],
        'endswith' => ['needles' => [[1, 'needles']], 'subject' => [0, 'haystack'], 'result' => 'bool'],
        'substrcount' => ['needles' => [[1, 'needle']], 'subject' => [0, 'haystack'], 'result' => 'count'],
        'match' => ['needles' => [[0, 'pattern']], 'subject' => [1, 'subject'], 'pattern' => true, 'result' => 'string'],
        'matchall' => ['needles' => [[0, 'pattern']], 'subject' => [1, 'subject'], 'pattern' => true, 'result' => 'collection'],
        'ismatch' => ['needles' => [[0, 'pattern']], 'subject' => [1, 'value'], 'pattern' => true, 'result' => 'bool'],
        'test' => ['needles' => [[0, 'pattern']], 'subject' => [1, 'subject'], 'pattern' => true, 'result' => 'bool'],
    ];

    /** Functions of PHP that search a string. */
    public const array FUNCTIONS = [
        'preg_match' => ['needles' => [[0, 'pattern']], 'subject' => [1, 'subject'], 'pattern' => true, 'result' => 'count'],
        'preg_match_all' => ['needles' => [[0, 'pattern']], 'subject' => [1, 'subject'], 'pattern' => true, 'result' => 'count'],
        'str_contains' => ['needles' => [[1, 'needle']], 'subject' => [0, 'haystack'], 'result' => 'bool'],
        'str_starts_with' => ['needles' => [[1, 'needle']], 'subject' => [0, 'haystack'], 'result' => 'bool'],
        'str_ends_with' => ['needles' => [[1, 'needle']], 'subject' => [0, 'haystack'], 'result' => 'bool'],
        'strpos' => ['needles' => [[1, 'needle']], 'subject' => [0, 'haystack'], 'result' => 'position'],
        'stripos' => ['needles' => [[1, 'needle']], 'subject' => [0, 'haystack'], 'result' => 'position'],
        'mb_strpos' => ['needles' => [[1, 'needle']], 'subject' => [0, 'haystack'], 'result' => 'position'],
        'mb_stripos' => ['needles' => [[1, 'needle']], 'subject' => [0, 'haystack'], 'result' => 'position'],
        'substr_count' => ['needles' => [[1, 'needle']], 'subject' => [0, 'haystack'], 'result' => 'count'],
        'mb_substr_count' => ['needles' => [[1, 'needle']], 'subject' => [0, 'haystack'], 'result' => 'count'],
    ];

    /** The name of the method or function a call names literally, else null. */
    public static function nameOf(CallLike $node): ?string
    {
        return match (true) {
            ($node instanceof MethodCall || $node instanceof NullsafeMethodCall || $node instanceof StaticCall) && $node->name instanceof Identifier => $node->name->toString(),
            // `use function PHPUnit\Framework\assertTrue` makes the name fully qualified.
            $node instanceof FuncCall && $node->name instanceof Name => $node->name->getLast(),
            default => null,
        };
    }

    /**
     * The argument for a parameter: by its name, else at its position when that argument has no name.
     *
     * @param  list<Arg>  $arguments
     */
    public static function argument(array $arguments, int $position, string $name): ?Arg
    {
        $named = array_find($arguments, fn (Arg $argument): bool => $argument->name?->toString() === $name);
        $positional = $arguments[$position] ?? null;

        return $named ?? ($positional?->name === null ? $positional : null);
    }
}
