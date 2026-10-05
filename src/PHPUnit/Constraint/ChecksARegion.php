<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use Override;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/**
 * What every constraint of this package says when it fails: the path of the
 * region, what was asked, what was found, and the region itself.
 */
trait ChecksARegion
{
    #[Override]
    protected function matches(mixed $other): bool
    {
        return $this->holds(Html::of($other));
    }

    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return Html::of($other)->path().' '.$this->toString();
    }

    #[Override]
    protected function additionalFailureDescription(mixed $other): string
    {
        $html = Html::of($other);

        return sprintf("%s\n\nIn %s:\n%s", $this->found($html), $html->path(), $html->excerpt());
    }
}
