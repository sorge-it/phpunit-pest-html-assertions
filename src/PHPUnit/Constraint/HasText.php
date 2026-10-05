<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/** The text of the region itself, mostly after `within()`. */
final class HasText extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $text) {}

    public function toString(): string
    {
        return sprintf('has the text "%s"', $this->text);
    }

    public function holds(Html $html): bool
    {
        return $html->text() === $this->text;
    }

    public function found(Html $html): string
    {
        return sprintf('Its text is "%s".', $html->text());
    }
}
