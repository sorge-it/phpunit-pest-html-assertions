<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Fixtures\Tests;

/** Analysed by MarkupAsStringRuleTest, never run: TYPO3 keeps its tests in `Tests/`. */
final class UpperCaseTest
{
    public function check(object $response): void
    {
        $response->assertSeeHtml('<b>');
    }
}
