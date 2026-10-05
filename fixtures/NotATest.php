<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Fixtures;

/** Analysed by MarkupAsStringRuleTest and ClassAsStringRuleTest, never run: code outside a tests directory may compare markup. */
final class NotATest
{
    public function render(object $response, string $html): void
    {
        $response->assertSeeHtml('<b>');
        str_contains($html, 'lg:flex');
    }
}
