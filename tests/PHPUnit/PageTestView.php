<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPUnit;

use Illuminate\Testing\TestView;
use SorgeIt\PhpunitPestHtmlAssertions\Tests\Page;

/** The page as a TestView of Laravel, rendered already: the view engine is not installed here. */
final class PageTestView extends TestView
{
    public function __construct()
    {
        $this->rendered = Page::HTML;
    }
}
