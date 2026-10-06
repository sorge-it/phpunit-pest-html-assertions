<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit;

use LogicException;

/**
 * `eachMatch()` found no node: a loop over nothing would check nothing, so the
 * test errs instead of failing. Pest's `->not` catches a failure, not this.
 */
final class NoMatch extends LogicException {}
