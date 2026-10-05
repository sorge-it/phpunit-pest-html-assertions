<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use DOMElement;
use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/**
 * An attribute by the start of its name, which no CSS selector can ask for:
 * `wire:poll.2s` and `wire:poll.10s` are both a poll.
 */
final class HasSelectorAttributeNamed extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector, private readonly string $prefix) {}

    public function toString(): string
    {
        return sprintf('has one node matching "%s" with an attribute whose name starts with "%s"', $this->selector, $this->prefix);
    }

    public function holds(Html $html): bool
    {
        return array_any($this->names(One::element($html, $this->selector)), fn (string $name): bool => str_starts_with($name, $this->prefix));
    }

    public function found(Html $html): string
    {
        return sprintf('Its attributes are %s.', implode(', ', $this->names(One::element($html, $this->selector))) ?: 'none');
    }

    /** @return list<string> */
    private function names(DOMElement $node): array
    {
        $names = [];

        foreach ($node->attributes ?? [] as $attribute) {
            $names[] = $attribute->name;
        }

        return $names;
    }
}
