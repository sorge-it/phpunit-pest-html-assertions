<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use InvalidArgumentException;
use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/**
 * A check turned around, in place of PHPUnit's `LogicalNot`: that one turns
 * every verb of the sentence, so "has a node whose text contains" became
 * "does not have a node whose text does not contain".
 */
final class Not extends Constraint implements RegionCheck
{
    use ChecksARegion;

    public function __construct(private readonly Constraint&RegionCheck $check)
    {
        if (! str_starts_with($check->toString(), 'has ')) {
            throw new InvalidArgumentException(sprintf('Not turns a check that starts with "has", not "%s".', $check->toString()));
        }
    }

    public function toString(): string
    {
        return 'does not have '.mb_substr($this->check->toString(), 4);
    }

    public function holds(Html $html): bool
    {
        return ! $this->check->holds($html);
    }

    public function found(Html $html): string
    {
        return 'The region holds what the check rules out.';
    }
}
