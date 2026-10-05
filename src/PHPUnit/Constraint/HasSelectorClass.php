<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use DOMElement;
use InvalidArgumentException;
use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/**
 * The node carries every class asked for, among others and in any order. A
 * check on the whole `class` attribute breaks with each further class.
 */
final class HasSelectorClass extends Constraint implements RegionCheck
{
    use ChecksARegion;

    /** @var list<string> */
    private readonly array $wanted;

    public function __construct(private readonly string $selector, private readonly string $classes)
    {
        $this->wanted = $this->split($classes);

        if ($this->wanted === []) {
            throw new InvalidArgumentException('A check of classes names at least one class.');
        }
    }

    public function toString(): string
    {
        return sprintf('has one node matching "%s" with the class "%s"', $this->selector, $this->classes);
    }

    public function holds(Html $html): bool
    {
        return array_diff($this->wanted, $this->of(One::element($html, $this->selector))) === [];
    }

    public function found(Html $html): string
    {
        return sprintf('Its classes are "%s".', implode(' ', $this->of(One::element($html, $this->selector))));
    }

    /** @return list<string> */
    private function of(DOMElement $node): array
    {
        return $this->split($node->getAttribute('class'));
    }

    /** @return list<string> */
    private function split(string $classes): array
    {
        return preg_split('/\s+/', mb_trim($classes), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
