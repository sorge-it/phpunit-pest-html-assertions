<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

final class HasAnySelectorTextContaining extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector, private readonly string $text) {}

    public function toString(): string
    {
        return sprintf('has a node matching "%s" whose text contains "%s"', $this->selector, $this->text);
    }

    public function holds(Html $html): bool
    {
        return array_any($html->texts($this->selector), fn (string $text): bool => str_contains($text, $this->text));
    }

    public function found(Html $html): string
    {
        return Texts::found($html, $this->selector);
    }
}
