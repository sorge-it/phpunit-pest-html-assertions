<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use DOMElement;
use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/**
 * Disabled as a browser reads it: by its own attribute, or by a disabled
 * `fieldset` around it, unless it stands in that fieldset's first `legend`.
 */
final class IsDisabled extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $selector) {}

    public function toString(): string
    {
        return sprintf('has one disabled node matching "%s"', $this->selector);
    }

    public function holds(Html $html): bool
    {
        $node = One::element($html, $this->selector);

        if ($node->hasAttribute('disabled')) {
            return true;
        }

        for ($child = $node, $parent = $node->parentNode; $parent instanceof DOMElement; $child = $parent, $parent = $parent->parentNode) {
            if (mb_strtolower($parent->localName ?? '') === 'fieldset' && $parent->hasAttribute('disabled') && ! $this->isFirstLegend($child, $parent)) {
                return true;
            }
        }

        return false;
    }

    public function found(Html $html): string
    {
        return 'The node is enabled.';
    }

    private function isFirstLegend(DOMElement $child, DOMElement $fieldset): bool
    {
        foreach ($fieldset->childNodes as $node) {
            if ($node instanceof DOMElement && mb_strtolower($node->localName ?? '') === 'legend') {
                return $node->isSameNode($child);
            }
        }

        return false;
    }
}
