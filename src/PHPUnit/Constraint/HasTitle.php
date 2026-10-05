<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Text;

/** The `title` of the page, which a check of the page's text leaves out with the head. */
final class HasTitle extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $title) {}

    public function toString(): string
    {
        return sprintf('has the title "%s"', $this->title);
    }

    public function holds(Html $html): bool
    {
        return Text::of(One::element($html, 'title')) === $this->title;
    }

    public function found(Html $html): string
    {
        return sprintf('The title is "%s".', Text::of(One::element($html, 'title')));
    }
}
