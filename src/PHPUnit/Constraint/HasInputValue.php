<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use DOMElement;
use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Text;

/**
 * The value a field holds, found by its name: the `value` attribute of an
 * input (checked or not), the text of a textarea, the chosen option of a select.
 */
final class HasInputValue extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $name, private readonly string $value) {}

    public static function valueOf(DOMElement $field): string
    {
        return match (mb_strtolower($field->localName ?? '')) {
            'textarea' => Text::raw($field),
            'select' => HasSelectedOption::chosen($field),
            default => $field->getAttribute('value'),
        };
    }

    public function toString(): string
    {
        return sprintf('has one field named "%s" with the value "%s"', $this->name, $this->value);
    }

    public function holds(Html $html): bool
    {
        return self::valueOf(One::element($html, $this->selector())) === $this->value;
    }

    public function found(Html $html): string
    {
        return sprintf('Its value is "%s".', self::valueOf(One::element($html, $this->selector())));
    }

    private function selector(): string
    {
        $name = addcslashes($this->name, '"\\');

        return sprintf('input[name="%1$s"], textarea[name="%1$s"], select[name="%1$s"]', $name);
    }
}
