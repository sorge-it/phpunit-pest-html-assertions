<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

final class HasTextContaining extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $text) {}

    public function toString(): string
    {
        return sprintf('has a text that contains "%s"', $this->text);
    }

    public function holds(Html $html): bool
    {
        return str_contains($html->text(), $this->text);
    }

    public function found(Html $html): string
    {
        return sprintf('Its text is "%s".', mb_strimwidth($html->text(), 0, 300, '…'));
    }
}
