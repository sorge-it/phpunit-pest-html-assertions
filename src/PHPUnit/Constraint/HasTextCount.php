<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/** How often a text stands in the region: a subject in the head and nowhere else. */
final class HasTextCount extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $text, private readonly int $count) {}

    public function toString(): string
    {
        return sprintf('has the text "%s" %d times', $this->text, $this->count);
    }

    public function holds(Html $html): bool
    {
        return mb_substr_count($html->text(), $this->text) === $this->count;
    }

    public function found(Html $html): string
    {
        return sprintf('The text stands %d times.', mb_substr_count($html->text(), $this->text));
    }
}
