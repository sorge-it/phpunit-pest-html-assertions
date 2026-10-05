<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/** A check of a region that `Not` can turn around with a message of its own. */
interface RegionCheck
{
    public function holds(Html $html): bool;

    /** What the region holds instead, one sentence. */
    public function found(Html $html): string;

    /** What the check asks for, starting with "has". */
    public function toString(): string;
}
