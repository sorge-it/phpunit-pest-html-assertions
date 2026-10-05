<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit;

use DOMElement;
use DOMNode;
use DOMText;

/**
 * The text of a node as a reader gets it: no comment, and nothing of a script,
 * a style or the head of the page inside it. A node asked for by name keeps
 * its own text, so a check on `script` reads the script.
 */
final class Text
{
    private const array UNREAD = ['head', 'noscript', 'script', 'style', 'template'];

    /** White space collapsed to one space, and trimmed: what a check of text compares. */
    public static function of(DOMNode $node): string
    {
        return mb_trim((string) preg_replace('/\s+/u', ' ', self::raw($node)));
    }

    /** The text as the page holds it, line breaks included. */
    public static function raw(DOMNode $node): string
    {
        return self::collect($node, true);
    }

    private static function collect(DOMNode $node, bool $asked): string
    {
        if ($node instanceof DOMText) {
            return $node->data;
        }

        if ($node instanceof DOMElement && ! $asked && in_array(mb_strtolower($node->localName ?? ''), self::UNREAD, true)) {
            return '';
        }

        $text = '';

        foreach ($node->childNodes as $child) {
            $text .= self::collect($child, false);
        }

        return $text;
    }
}
