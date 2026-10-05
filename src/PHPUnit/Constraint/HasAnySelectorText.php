<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

final class HasAnySelectorText extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector, private readonly string $text) {}

    public function toString(): string
    {
        return sprintf('has a node matching "%s" with the text "%s"', $this->selector, $this->text);
    }

    public function holds(Html $html): bool
    {
        return in_array($this->text, $html->texts($this->selector), true);
    }

    public function found(Html $html): string
    {
        return Texts::found($html, $this->selector);
    }
}
