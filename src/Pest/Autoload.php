<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Pest;

use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;

/**
 * The page as a region, so that `within()`, `frame()`, `texts()`,
 * `rawTexts()` and `attributes()` run on it: Pest hands a method it does
 * not know to the object it expects, and keeps checking the result.
 */
function html(mixed $value): Html
{
    return Html::of($value);
}

// Composer loads this file in every process that has the package; outside of
// Pest there is no `expect()` to extend.
if (function_exists('expect')) {
    Expectations::register();
}
