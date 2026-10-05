<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Text;

final class HasSelectorText extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector, private readonly string $text) {}

    public function toString(): string
    {
        return sprintf('has one node matching "%s" with the text "%s"', $this->selector, $this->text);
    }

    public function holds(Html $html): bool
    {
        return Text::of(One::element($html, $this->selector)) === $this->text;
    }

    public function found(Html $html): string
    {
        return sprintf('Its text is "%s".', Text::of(One::element($html, $this->selector)));
    }
}
