<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Pest;

use Pest\Expectation;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
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
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

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

        expect()->extend('toHaveSelector', fn (string $selector): Expectation => $check($this, new HasSelector($selector)));
        expect()->extend('toHaveSelectorCount', fn (string $selector, int $count): Expectation => $check($this, new HasSelectorCount($selector, $count)));
        expect()->extend('toHaveSelectorCountAtLeast', fn (string $selector, int $count): Expectation => $check($this, new HasSelectorCountAtLeast($selector, $count)));
        expect()->extend('toHaveSelectorText', fn (string $selector, string $text): Expectation => $check($this, new HasSelectorText($selector, $text)));
        expect()->extend('toHaveSelectorTextContaining', fn (string $selector, string $text): Expectation => $check($this, new HasSelectorTextContaining($selector, $text)));
        expect()->extend('toHaveAnySelectorText', fn (string $selector, string $text): Expectation => $check($this, new HasAnySelectorText($selector, $text)));
        expect()->extend('toHaveAnySelectorTextContaining', fn (string $selector, string $text): Expectation => $check($this, new HasAnySelectorTextContaining($selector, $text)));
        expect()->extend('toHaveSelectorAttribute', fn (string $selector, string $attribute, ?string $value = null): Expectation => $check($this, new HasSelectorAttribute($selector, $attribute, $value)));
        expect()->extend('toHaveSelectorAttributeContaining', fn (string $selector, string $attribute, string $part): Expectation => $check($this, new HasSelectorAttributeContaining($selector, $attribute, $part)));
        expect()->extend('toHaveSelectorAttributeNamed', fn (string $selector, string $prefix): Expectation => $check($this, new HasSelectorAttributeNamed($selector, $prefix)));
        expect()->extend('toHaveSelectorClass', fn (string $selector, string $classes): Expectation => $check($this, new HasSelectorClass($selector, $classes)));
        expect()->extend('toHaveText', fn (string $text): Expectation => $check($this, new HasText($text)));
        expect()->extend('toHaveTextContaining', fn (string $text): Expectation => $check($this, new HasTextContaining($text)));
        expect()->extend('toHaveTextCount', fn (string $text, int $count): Expectation => $check($this, new HasTextCount($text, $count)));
        expect()->extend('toAppearBefore', fn (string $first, string $second): Expectation => $check($this, new AppearsBefore($first, $second)));
        expect()->extend('toHaveTitle', fn (string $title): Expectation => $check($this, new HasTitle($title)));
        expect()->extend('toHaveInputValue', fn (string $name, string $value): Expectation => $check($this, new HasInputValue($name, $value)));
        expect()->extend('toBeChecked', fn (string $selector): Expectation => $check($this, new IsChecked($selector)));
        expect()->extend('toHaveSelectedOption', fn (string $selector, string $value): Expectation => $check($this, new HasSelectedOption($selector, $value)));
        expect()->extend('toBeDisabled', fn (string $selector): Expectation => $check($this, new IsDisabled($selector)));
        expect()->extend('toHaveLink', fn (string $text, string $href): Expectation => $check($this, new HasLink($text, $href)));
        expect()->extend('toBeEmptyNode', fn (string $selector): Expectation => $check($this, new IsEmpty($selector)));

        expect()->extend('eachMatch', fn (string $selector, callable $callback): Expectation => $eachMatch($this, $selector, $callback));
    }

    /**
     * @param  Expectation<mixed>  $expectation
     * @return Expectation<mixed>
     */
    private static function check(Expectation $expectation, Constraint $constraint): Expectation
    {
        Assert::assertThat(Html::of($expectation->value), $constraint);

        return $expectation;
    }

    /**
     * The same checks on every node the selector finds. No match is a
     * failure: a loop over nothing would pass and check nothing.
     *
     * @param  Expectation<mixed>  $expectation
     * @param  callable(Expectation<Html|null>, int): mixed  $callback
     * @return Expectation<mixed>
     */
    private static function eachMatch(Expectation $expectation, string $selector, callable $callback): Expectation
    {
        $html = Html::of($expectation->value);

        Assert::assertThat($html, new HasSelector($selector));

        foreach ($html->matches($selector) as $index => $region) {
            $callback(expect($region), $index);
        }

        return $expectation;
    }
}
