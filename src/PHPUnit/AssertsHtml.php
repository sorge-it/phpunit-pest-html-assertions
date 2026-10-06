<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit;

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
        Assertion::that($html, $constraint, $message);
    }

    public static function assertHtmlNot(mixed $html, Constraint&RegionCheck $constraint, string $message = ''): void
    {
        Assertion::that($html, new Not($constraint), $message);
    }

    public static function assertHtmlSelectorExists(mixed $html, string $selector, string $message = ''): void
    {
        self::assertHtml($html, new HasSelector($selector), $message);
    }

    public static function assertHtmlSelectorNotExists(mixed $html, string $selector, string $message = ''): void
    {
        self::assertHtmlNot($html, new HasSelector($selector), $message);
    }

    public static function assertHtmlSelectorCount(mixed $html, string $selector, int $count, string $message = ''): void
    {
        self::assertHtml($html, new HasSelectorCount($selector, $count), $message);
    }

    public static function assertHtmlSelectorCountAtLeast(mixed $html, string $selector, int $count, string $message = ''): void
    {
        self::assertHtml($html, new HasSelectorCountAtLeast($selector, $count), $message);
    }

    public static function assertHtmlSelectorTextSame(mixed $html, string $selector, string $text, string $message = ''): void
    {
        self::assertHtml($html, new HasSelectorText($selector, $text), $message);
    }

    public static function assertHtmlSelectorTextContains(mixed $html, string $selector, string $text, string $message = ''): void
    {
        self::assertHtml($html, new HasSelectorTextContaining($selector, $text), $message);
    }

    public static function assertHtmlAnySelectorTextSame(mixed $html, string $selector, string $text, string $message = ''): void
    {
        self::assertHtml($html, new HasAnySelectorText($selector, $text), $message);
    }

    public static function assertHtmlAnySelectorTextContains(mixed $html, string $selector, string $text, string $message = ''): void
    {
        self::assertHtml($html, new HasAnySelectorTextContaining($selector, $text), $message);
    }

    public static function assertHtmlSelectorAttribute(mixed $html, string $selector, string $attribute, ?string $value = null, string $message = ''): void
    {
        self::assertHtml($html, new HasSelectorAttribute($selector, $attribute, $value), $message);
    }

    public static function assertHtmlSelectorAttributeContains(mixed $html, string $selector, string $attribute, string $part, string $message = ''): void
    {
        self::assertHtml($html, new HasSelectorAttributeContaining($selector, $attribute, $part), $message);
    }

    public static function assertHtmlSelectorAttributeNamed(mixed $html, string $selector, string $prefix, string $message = ''): void
    {
        self::assertHtml($html, new HasSelectorAttributeNamed($selector, $prefix), $message);
    }

    public static function assertHtmlSelectorClass(mixed $html, string $selector, string $classes, string $message = ''): void
    {
        self::assertHtml($html, new HasSelectorClass($selector, $classes), $message);
    }

    public static function assertHtmlTextSame(mixed $html, string $text, string $message = ''): void
    {
        self::assertHtml($html, new HasText($text), $message);
    }

    public static function assertHtmlTextContains(mixed $html, string $text, string $message = ''): void
    {
        self::assertHtml($html, new HasTextContaining($text), $message);
    }

    public static function assertHtmlTextCount(mixed $html, string $text, int $count, string $message = ''): void
    {
        self::assertHtml($html, new HasTextCount($text, $count), $message);
    }

    public static function assertHtmlSelectorBefore(mixed $html, string $first, string $second, string $message = ''): void
    {
        self::assertHtml($html, new AppearsBefore($first, $second), $message);
    }

    public static function assertHtmlPageTitleSame(mixed $html, string $title, string $message = ''): void
    {
        self::assertHtml($html, new HasTitle($title), $message);
    }

    public static function assertHtmlInputValueSame(mixed $html, string $name, string $value, string $message = ''): void
    {
        self::assertHtml($html, new HasInputValue($name, $value), $message);
    }

    public static function assertHtmlCheckboxChecked(mixed $html, string $selector, string $message = ''): void
    {
        self::assertHtml($html, new IsChecked($selector), $message);
    }

    public static function assertHtmlSelectedOption(mixed $html, string $selector, string $value, string $message = ''): void
    {
        self::assertHtml($html, new HasSelectedOption($selector, $value), $message);
    }

    public static function assertHtmlSelectorDisabled(mixed $html, string $selector, string $message = ''): void
    {
        self::assertHtml($html, new IsDisabled($selector), $message);
    }

    public static function assertHtmlLink(mixed $html, string $text, string $href, string $message = ''): void
    {
        self::assertHtml($html, new HasLink($text, $href), $message);
    }

    public static function assertHtmlSelectorEmpty(mixed $html, string $selector, string $message = ''): void
    {
        self::assertHtml($html, new IsEmpty($selector), $message);
    }
}
