<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

final class IsChecked extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector) {}

    public function toString(): string
    {
        return sprintf('has one checked box matching "%s"', $this->selector);
    }

    public function holds(Html $html): bool
    {
        return One::element($html, $this->selector)->hasAttribute('checked');
    }

    public function found(Html $html): string
    {
        return 'The box is not checked.';
    }
}
