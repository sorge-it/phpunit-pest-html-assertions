<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use DOMElement;
use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Text;

/** The option a select holds: of several marked `selected` the last, of none the first, as a browser does. */
final class HasSelectedOption extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector, private readonly string $value) {}

    /** The value of the chosen option, or its text where it has no value. */
    public static function chosen(DOMElement $select): string
    {
        $options = [];

        foreach ($select->getElementsByTagName('option') as $option) {
            $options[] = $option;
        }

        $selected = array_values(array_filter($options, fn (DOMElement $option): bool => $option->hasAttribute('selected')));
        $chosen = array_last($selected) ?? array_first($options);

        return match (true) {
            $chosen === null => '',
            $chosen->hasAttribute('value') => $chosen->getAttribute('value'),
            default => Text::of($chosen),
        };
    }

    public function toString(): string
    {
        return sprintf('has one select matching "%s" with the option "%s" chosen', $this->selector, $this->value);
    }

    public function holds(Html $html): bool
    {
        return self::chosen(One::element($html, $this->selector)) === $this->value;
    }

    public function found(Html $html): string
    {
        return sprintf('The chosen option is "%s".', self::chosen(One::element($html, $this->selector)));
    }
}
