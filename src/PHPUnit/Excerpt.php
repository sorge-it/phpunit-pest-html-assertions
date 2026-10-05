<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit;

use DOMElement;
use DOMNode;
use DOMText;

/**
 * A region as indented HTML for a failure message: one element or one text
 * per line, long values cut, and no more lines than a reader takes in.
 */
final class Excerpt
{
    private const int LINES = 40;

    private const int WIDTH = 100;

    /** @param list<DOMNode> $nodes */
    public static function of(array $nodes): string
    {
        $lines = [];

        foreach ($nodes as $node) {
            self::write($node, 0, $lines);
        }

        $more = count($lines) - self::LINES;

        if ($more > 0) {
            $lines = [...array_slice($lines, 0, self::LINES), sprintf('… %d more lines', $more)];
        }

        return implode("\n", $lines);
    }

    /** @param list<string> $lines */
    private static function write(DOMNode $node, int $depth, array &$lines): void
    {
        $indent = str_repeat('  ', $depth + 1);

        if ($node instanceof DOMText) {
            $text = mb_trim((string) preg_replace('/\s+/u', ' ', $node->data));

            if ($text !== '') {
                $lines[] = $indent.self::cut($text);
            }

            return;
        }

        if (! $node instanceof DOMElement) {
            return;
        }

        $lines[] = $indent.self::cut(self::openingTag($node));

        foreach ($node->childNodes as $child) {
            self::write($child, $depth + 1, $lines);
        }
    }

    private static function openingTag(DOMElement $element): string
    {
        $attributes = '';

        foreach ($element->attributes ?? [] as $attribute) {
            $value = self::cut($attribute->value, 40);
            $attributes .= $value === '' ? ' '.$attribute->name : sprintf(' %s="%s"', $attribute->name, $value);
        }

        return sprintf('<%s%s>', $element->localName, $attributes);
    }

    private static function cut(string $text, int $width = self::WIDTH): string
    {
        return mb_strlen($text) > $width ? mb_substr($text, 0, $width - 1).'…' : $text;
    }
}
