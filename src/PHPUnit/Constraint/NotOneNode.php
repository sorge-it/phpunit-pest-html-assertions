<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint;

use LogicException;

/** A check of one node found none or several: the selector of the test is wrong, so the test errs instead of failing. */
final class NotOneNode extends LogicException {}
