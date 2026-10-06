<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit;

use LogicException;

/**
 * A check got a value that is not a page: the test is wrong, so it errs instead
 * of failing. Pest's `->not` catches a failure, and a negated check must not pass.
 */
final class NotAPage extends LogicException {}
