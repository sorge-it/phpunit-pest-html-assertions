<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPUnit;

use Illuminate\Testing\TestComponent;
use SorgeIt\PhpunitPestHtmlAssertions\Tests\Page;

/** The page as a TestComponent of Laravel, rendered already: the view engine is not installed here. */
final class PageTestComponent extends TestComponent
{
    public function __construct()
    {
        $this->rendered = Page::HTML;
    }
}
