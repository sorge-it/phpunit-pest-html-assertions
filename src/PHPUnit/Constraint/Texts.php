<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/** The texts a check of "some node" found, for its failure message. */
final class Texts
{
    private const int SHOWN = 10;

    public static function found(Html $html, string $selector): string
    {
        $texts = $html->texts($selector);

        if ($texts === []) {
            return One::count($html, $selector);
        }

        $shown = array_map(fn (string $text): string => sprintf('"%s"', $text), array_slice($texts, 0, self::SHOWN));
        $more = count($texts) > self::SHOWN ? sprintf(' and %d more', count($texts) - self::SHOWN) : '';

        return sprintf('The nodes hold %s%s.', implode(', ', $shown), $more);
    }
}
