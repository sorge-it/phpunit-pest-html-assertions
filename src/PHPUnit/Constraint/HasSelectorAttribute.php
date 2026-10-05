<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/** Without a value, the attribute only has to be there: `disabled`, `required`, a `data-*` marker. */
final class HasSelectorAttribute extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector, private readonly string $attribute, private readonly ?string $value = null) {}

    public function toString(): string
    {
        return $this->value === null
            ? sprintf('has one node matching "%s" with the attribute "%s"', $this->selector, $this->attribute)
            : sprintf('has one node matching "%s" whose attribute "%s" is "%s"', $this->selector, $this->attribute, $this->value);
    }

    public function holds(Html $html): bool
    {
        $node = One::element($html, $this->selector);

        return $node->hasAttribute($this->attribute) && ($this->value === null || $node->getAttribute($this->attribute) === $this->value);
    }

    public function found(Html $html): string
    {
        $node = One::element($html, $this->selector);

        return $node->hasAttribute($this->attribute)
            ? sprintf('The attribute is "%s".', $node->getAttribute($this->attribute))
            : sprintf('The node has no attribute "%s".', $this->attribute);
    }
}
