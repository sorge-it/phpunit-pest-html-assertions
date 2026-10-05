<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/** Two nodes in the order of the page; each selector finds exactly one. */
final class AppearsBefore extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $first, private readonly string $second) {}

    public function toString(): string
    {
        return sprintf('has the node matching "%s" before the node matching "%s"', $this->first, $this->second);
    }

    public function holds(Html $html): bool
    {
        return $this->position(One::element($html, $this->first)) < $this->position(One::element($html, $this->second));
    }

    public function found(Html $html): string
    {
        return sprintf('The node matching "%s" stands first.', $this->second);
    }

    /** The place of an element in the order of the document. */
    private function position(DOMElement $node): int
    {
        $document = $node->ownerDocument;
        $position = $document instanceof DOMDocument ? new DOMXPath($document)->evaluate('count(preceding::*) + count(ancestor::*)', $node) : 0;

        return is_float($position) ? (int) $position : 0;
    }
}
