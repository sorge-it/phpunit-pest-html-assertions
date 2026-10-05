<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Fixtures;

/** Analysed by MarkupAsStringRuleTest, never run: code outside a tests directory may compare markup. */
final class NotATest
{
    public function render(object $response): void
    {
        $response->assertSeeHtml('<b>');
    }
}
