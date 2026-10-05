<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit;

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
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\Not;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\RegionCheck;

/**
 * The checks of this package for a PHPUnit test case. Each takes what
 * `Html::of()` takes; a check turns around with `assertHtmlNot()`.
 */
trait AssertsHtml
{
    public static function assertHtml(mixed $html, Constraint $constraint, string $message = ''): void
    {
        Assert::assertThat(Html::of($html), $constraint, $message);
    }

    public static function assertHtmlNot(mixed $html, Constraint&RegionCheck $constraint, string $message = ''): void
    {
        Assert::assertThat(Html::of($html), new Not($constraint), $message);
    }

    public static function assertHtmlSelectorExists(mixed $html, string $selector): void
    {
        self::assertHtml($html, new HasSelector($selector));
    }

    public static function assertHtmlSelectorNotExists(mixed $html, string $selector): void
    {
        self::assertHtmlNot($html, new HasSelector($selector));
    }

    public static function assertHtmlSelectorCount(mixed $html, string $selector, int $count): void
    {
        self::assertHtml($html, new HasSelectorCount($selector, $count));
    }

    public static function assertHtmlSelectorCountAtLeast(mixed $html, string $selector, int $count): void
    {
        self::assertHtml($html, new HasSelectorCountAtLeast($selector, $count));
    }

    public static function assertHtmlSelectorTextSame(mixed $html, string $selector, string $text): void
    {
        self::assertHtml($html, new HasSelectorText($selector, $text));
    }

    public static function assertHtmlSelectorTextContains(mixed $html, string $selector, string $text): void
    {
        self::assertHtml($html, new HasSelectorTextContaining($selector, $text));
    }

    public static function assertHtmlAnySelectorTextSame(mixed $html, string $selector, string $text): void
    {
        self::assertHtml($html, new HasAnySelectorText($selector, $text));
    }

    public static function assertHtmlAnySelectorTextContains(mixed $html, string $selector, string $text): void
    {
        self::assertHtml($html, new HasAnySelectorTextContaining($selector, $text));
    }

    public static function assertHtmlSelectorAttribute(mixed $html, string $selector, string $attribute, ?string $value = null): void
    {
        self::assertHtml($html, new HasSelectorAttribute($selector, $attribute, $value));
    }

    public static function assertHtmlSelectorAttributeContains(mixed $html, string $selector, string $attribute, string $part): void
    {
        self::assertHtml($html, new HasSelectorAttributeContaining($selector, $attribute, $part));
    }

    public static function assertHtmlSelectorAttributeNamed(mixed $html, string $selector, string $prefix): void
    {
        self::assertHtml($html, new HasSelectorAttributeNamed($selector, $prefix));
    }

    public static function assertHtmlSelectorClass(mixed $html, string $selector, string $classes): void
    {
        self::assertHtml($html, new HasSelectorClass($selector, $classes));
    }

    public static function assertHtmlTextSame(mixed $html, string $text): void
    {
        self::assertHtml($html, new HasText($text));
    }

    public static function assertHtmlTextContains(mixed $html, string $text): void
    {
        self::assertHtml($html, new HasTextContaining($text));
    }

    public static function assertHtmlTextCount(mixed $html, string $text, int $count): void
    {
        self::assertHtml($html, new HasTextCount($text, $count));
    }

    public static function assertHtmlSelectorBefore(mixed $html, string $first, string $second): void
    {
        self::assertHtml($html, new AppearsBefore($first, $second));
    }

    public static function assertHtmlPageTitleSame(mixed $html, string $title): void
    {
        self::assertHtml($html, new HasTitle($title));
    }

    public static function assertHtmlInputValueSame(mixed $html, string $name, string $value): void
    {
        self::assertHtml($html, new HasInputValue($name, $value));
    }

    public static function assertHtmlCheckboxChecked(mixed $html, string $selector): void
    {
        self::assertHtml($html, new IsChecked($selector));
    }

    public static function assertHtmlSelectedOption(mixed $html, string $selector, string $value): void
    {
        self::assertHtml($html, new HasSelectedOption($selector, $value));
    }

    public static function assertHtmlSelectorDisabled(mixed $html, string $selector): void
    {
        self::assertHtml($html, new IsDisabled($selector));
    }

    public static function assertHtmlLink(mixed $html, string $text, string $href): void
    {
        self::assertHtml($html, new HasLink($text, $href));
    }

    public static function assertHtmlSelectorEmpty(mixed $html, string $selector): void
    {
        self::assertHtml($html, new IsEmpty($selector));
    }
}
