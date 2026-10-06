<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use DOMElement;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/**
 * The one node a check of a single node reads. Zero or two matches stop the
 * check with `NotOneNode`, which no `->not` turns around: Pest's catches every
 * `AssertionFailedError`, and a negated check must not pass on a missing node.
 */
final class One
{
    public static function element(Html $html, string $selector, string $check = 'a check of one node'): DOMElement
    {
        $elements = $html->elements($selector);

        if (count($elements) !== 1) {
            throw new NotOneNode(sprintf(
                "%s: %s needs exactly one match. %s\n\nIn %s:\n%s",
                $html->path(),
                $check,
                self::count($html, $selector),
                $html->path(),
                $html->excerpt(),
            ));
        }

        return $elements[0];
    }

    public static function count(Html $html, string $selector): string
    {
        return sprintf('The selector "%s" finds %d nodes.', $selector, $html->count($selector));
    }
}
