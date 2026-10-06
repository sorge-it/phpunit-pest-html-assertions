<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Pest;

use LogicException;
use Pest\Expectation;
use Pest\Expectations\OppositeExpectation;
use PHPUnit\Framework\Constraint\Constraint;
use ReflectionFunction;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Assertion;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\AppearsBefore;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasAnySelectorText;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasAnySelectorTextContaining;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasInputValue;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasLink;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectedOption;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelector;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorAttribute;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorAttributeContaining;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorAttributeNamed;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorClass;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorCount;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorCountAtLeast;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorText;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorTextContaining;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasText;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasTextContaining;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasTextCount;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasTitle;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\IsChecked;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\IsDisabled;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\IsEmpty;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\One;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\NoMatch;

/**
 * The constraints of the PHPUnit part as Pest expectations. `->not` turns any
 * check around.
 */
final class Expectations
{
    public static function register(): void
    {
        // Pest binds each closure to the expectation, so `self` in it is not this class.
        $check = self::check(...);
        $eachMatch = self::eachMatch(...);

        expect()->extend('toHaveSelector', fn (string $selector, string $message = ''): Expectation => $check($this, new HasSelector($selector), $message));
        expect()->extend('toHaveSelectorCount', fn (string $selector, int $count, string $message = ''): Expectation => $check($this, new HasSelectorCount($selector, $count), $message));
        expect()->extend('toHaveSelectorCountAtLeast', fn (string $selector, int $count, string $message = ''): Expectation => $check($this, new HasSelectorCountAtLeast($selector, $count), $message));
        expect()->extend('toHaveSelectorText', fn (string $selector, string $text, string $message = ''): Expectation => $check($this, new HasSelectorText($selector, $text), $message));
        expect()->extend('toHaveSelectorTextContaining', fn (string $selector, string $text, string $message = ''): Expectation => $check($this, new HasSelectorTextContaining($selector, $text), $message));
        expect()->extend('toHaveAnySelectorText', fn (string $selector, string $text, string $message = ''): Expectation => $check($this, new HasAnySelectorText($selector, $text), $message));
        expect()->extend('toHaveAnySelectorTextContaining', fn (string $selector, string $text, string $message = ''): Expectation => $check($this, new HasAnySelectorTextContaining($selector, $text), $message));
        expect()->extend('toHaveSelectorAttribute', fn (string $selector, string $attribute, ?string $value = null, string $message = ''): Expectation => $check($this, new HasSelectorAttribute($selector, $attribute, $value), $message));
        expect()->extend('toHaveSelectorAttributeContaining', fn (string $selector, string $attribute, string $part, string $message = ''): Expectation => $check($this, new HasSelectorAttributeContaining($selector, $attribute, $part), $message));
        expect()->extend('toHaveSelectorAttributeNamed', fn (string $selector, string $prefix, string $message = ''): Expectation => $check($this, new HasSelectorAttributeNamed($selector, $prefix), $message));
        expect()->extend('toHaveSelectorClass', fn (string $selector, string $classes, string $message = ''): Expectation => $check($this, new HasSelectorClass($selector, $classes), $message));
        expect()->extend('toHaveText', fn (string $text, string $message = ''): Expectation => $check($this, new HasText($text), $message));
        expect()->extend('toHaveTextContaining', fn (string $text, string $message = ''): Expectation => $check($this, new HasTextContaining($text), $message));
        expect()->extend('toHaveTextCount', fn (string $text, int $count, string $message = ''): Expectation => $check($this, new HasTextCount($text, $count), $message));
        expect()->extend('toAppearBefore', fn (string $first, string $second, string $message = ''): Expectation => $check($this, new AppearsBefore($first, $second), $message));
        expect()->extend('toHaveTitle', fn (string $title, string $message = ''): Expectation => $check($this, new HasTitle($title), $message));
        expect()->extend('toHaveInputValue', fn (string $name, string $value, string $message = ''): Expectation => $check($this, new HasInputValue($name, $value), $message));
        expect()->extend('toBeChecked', fn (string $selector, string $message = ''): Expectation => $check($this, new IsChecked($selector), $message));
        expect()->extend('toHaveSelectedOption', fn (string $selector, string $value, string $message = ''): Expectation => $check($this, new HasSelectedOption($selector, $value), $message));
        expect()->extend('toBeDisabled', fn (string $selector, string $message = ''): Expectation => $check($this, new IsDisabled($selector), $message));
        expect()->extend('toHaveLink', fn (string $text, string $href, string $message = ''): Expectation => $check($this, new HasLink($text, $href), $message));
        expect()->extend('toBeEmptyNode', fn (string $selector, string $message = ''): Expectation => $check($this, new IsEmpty($selector), $message));

        expect()->extend('eachMatch', fn (string $selector, callable $callback, string $message = ''): Expectation => $eachMatch($this, $selector, $callback, $message));
    }

    /**
     * @param  Expectation<mixed>  $expectation
     * @return Expectation<mixed>
     */
    private static function check(Expectation $expectation, Constraint $constraint, string $message): Expectation
    {
        Assertion::that($expectation->value, $constraint, $message);

        return $expectation;
    }

    /**
     * The same checks on every node the selector finds. No match stops the
     * test with `NoMatch`: a loop over nothing would pass, also under `->not`.
     *
     * @param  Expectation<mixed>  $expectation
     * @param  callable(Expectation<Html|null>, int): mixed  $callback
     * @return Expectation<mixed>
     */
    private static function eachMatch(Expectation $expectation, string $selector, callable $callback, string $message): Expectation
    {
        if (self::isTurnedAround()) {
            throw new LogicException(($message === '' ? '' : $message."\n").'eachMatch() cannot be turned around: ->not would pass where one match fails. Turn the checks in the callback around.');
        }

        $html = Assertion::html($expectation->value, $message);

        if ($html->count($selector) === 0) {
            throw new NoMatch(sprintf(
                "%s%s: eachMatch() needs at least one match. %s\n\nIn %s:\n%s",
                $message === '' ? '' : $message."\n",
                $html->path(),
                One::count($html, $selector),
                $html->path(),
                $html->excerpt(),
            ));
        }

        Assertion::that($html, new HasSelector($selector), $message);

        foreach ($html->matches($selector) as $index => $region) {
            $callback(expect($region), $index);
        }

        return $expectation;
    }

    /**
     * Whether Pest's `->not` called this expectation. Pest's own frames lie in
     * between; the first frame outside Pest decides. A test that wraps the
     * call, for example in `->not->toThrow()`, stops the search with its closure.
     */
    private static function isTurnedAround(): bool
    {
        foreach (array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1) as $frame) {
            $class = $frame['class'] ?? null;

            if ($class === OppositeExpectation::class) {
                return true;
            }

            // Pest calls through `call_user_func_array()`; a function of the test is a frame outside Pest.
            $outsidePest = $class === null
                ? ! function_exists($frame['function']) || ! (new ReflectionFunction($frame['function']))->isInternal()
                : $class !== self::class && ! str_starts_with($class, 'Pest\\');

            if ($outsidePest) {
                return false;
            }
        }

        return false;
    }
}
