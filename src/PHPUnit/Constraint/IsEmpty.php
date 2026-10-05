<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Text;

/** No element and no text inside the node; white space does not count. */
final class IsEmpty extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector) {}

    public function toString(): string
    {
        return sprintf('has one empty node matching "%s"', $this->selector);
    }

    public function holds(Html $html): bool
    {
        $node = One::element($html, $this->selector);

        return $node->childElementCount === 0 && Text::of($node) === '';
    }

    public function found(Html $html): string
    {
        $node = One::element($html, $this->selector);

        return sprintf('It holds %d elements and the text "%s".', $node->childElementCount, Text::of($node));
    }
}
