<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use DOMElement;
use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Text;

/** A link a reader can follow: its text and its target, both exact. */
final class HasLink extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly string $text, private readonly string $href) {}

    public function toString(): string
    {
        return sprintf('has a link "%s" to "%s"', $this->text, $this->href);
    }

    public function holds(Html $html): bool
    {
        return array_any(
            $html->elements('a[href]'),
            fn (DOMElement $link): bool => Text::of($link) === $this->text && $link->getAttribute('href') === $this->href,
        );
    }

    public function found(Html $html): string
    {
        $links = array_map(
            fn (DOMElement $link): string => sprintf('"%s" to "%s"', Text::of($link), $link->getAttribute('href')),
            $html->elements('a[href]'),
        );

        return $links === [] ? 'The region has no link.' : sprintf('The links are %s.', implode(', ', array_slice($links, 0, 10)));
    }
}
