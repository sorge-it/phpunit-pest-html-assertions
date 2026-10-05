<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

final class HasSelector extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector) {}

    public function toString(): string
    {
        return sprintf('has a node matching "%s"', $this->selector);
    }

    public function holds(Html $html): bool
    {
        return $html->count($this->selector) > 0;
    }

    public function found(Html $html): string
    {
        return One::count($html, $this->selector);
    }
}
