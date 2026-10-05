<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

final class HasSelectorAttributeContaining extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector, private readonly string $attribute, private readonly string $part) {}

    public function toString(): string
    {
        return sprintf('has one node matching "%s" whose attribute "%s" contains "%s"', $this->selector, $this->attribute, $this->part);
    }

    public function holds(Html $html): bool
    {
        $node = One::element($html, $this->selector);

        return $node->hasAttribute($this->attribute) && str_contains($node->getAttribute($this->attribute), $this->part);
    }

    public function found(Html $html): string
    {
        $node = One::element($html, $this->selector);

        return $node->hasAttribute($this->attribute)
            ? sprintf('The attribute is "%s".', $node->getAttribute($this->attribute))
            : sprintf('The node has no attribute "%s".', $this->attribute);
    }
}
