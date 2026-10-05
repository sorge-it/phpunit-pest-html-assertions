<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

final class HasSelectorCountAtLeast extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector, private readonly int $count) {}

    public function toString(): string
    {
        return sprintf('has at least %d nodes matching "%s"', $this->count, $this->selector);
    }

    public function holds(Html $html): bool
    {
        return $html->count($this->selector) >= $this->count;
    }

    public function found(Html $html): string
    {
        return One::count($html, $this->selector);
    }
}
