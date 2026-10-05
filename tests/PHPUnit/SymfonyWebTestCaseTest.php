<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPUnit;

use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\AssertsHtml;
use SorgeIt\PhpunitPestHtmlAssertions\Tests\Page;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * The trait in the test case of a Symfony app. Symfony declares `assertSelectorExists()` and nine
 * more with the same names and other parameters; a trait method of that name would end the run
 * with a fatal error before the first test.
 */
final class SymfonyWebTestCaseTest extends WebTestCase
{
    use AssertsHtml;

    public function test_it_checks_a_page_beside_the_assertions_of_symfony(): void
    {
        self::assertHtmlSelectorExists(Page::HTML, 'h1');
        self::assertHtmlSelectorTextSame(Page::HTML, 'h1', 'Review on Friday');
    }
}
