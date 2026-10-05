<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPStan\data\tests;

use Illuminate\Support\Str;

/** Analysed by MarkupAsStringRuleTest, never run. Each line names whether the rule reports it. */
final class StringChecks
{
    public function check(object $response, object $expectation, string $html, string $id): void
    {
        $response->assertSeeHtml('<b>');                              // reported
        $response->assertDontSeeHtml('x');                            // reported: the call itself is the problem
        $response->assertSeeInOrder(['a', 'b']);                       // reported
        $response->assertSeeHtmlInOrder(['<b>', '<i>']);               // reported
        $response->assertSee('Review');                               // text: not reported
        $response->assertSee('<span class="x">', false);               // reported: escaping off
        $response->assertDontSee('<b>', escape: false);                // reported: escaping off
        $expectation->toContain('<code>read</code>');                  // reported
        $expectation->toContain('href="'.$id.'"');                     // reported
        $expectation->toBe("<li data-name=\"{$id}\">");                 // reported
        $expectation->toStartWith('<p class="x">');                    // reported
        $expectation->toEndWith('</p>');                               // reported
        $expectation->toMatch('/<div[^>]*>/');                         // reported
        $expectation->toContain('wire:poll.10s');                      // reported: the name of an attribute
        $expectation->toContain('data-stale');                         // reported: the name of an attribute
        $expectation->toContain('Review on Friday');                 // text: not reported
        $expectation->toContain('-space-x-');                          // a class name alone: not reported
        $expectation->toContain('Antwort > Frage');                    // text with a bracket: not reported
        $expectation->toBe('https://example.test/data-export');        // a path: not reported
        $expectation->toBe('Siehe key="value" im Log');                // no attribute of HTML: not reported
        $this->assertStringContainsString('<div', $html);             // reported
        self::assertMatchesRegularExpression('/data-x="1"/', $html);   // reported
        Str::between($html, 'data-people', '</ul>');                   // reported
        Str::after($html, 'class="card-body');              // reported
        Str::afterLast($html, '<li>');                                 // reported
        Str::beforeLast($html, '</ul>');                               // reported
        Str::betweenFirst($html, 'x-data', '>');                       // reported: cut at the end of a tag
        Str::contains($html, 'data-here');                             // reported
        Str::of($html)->between('<ul>', '</ul>');                      // reported
        Str::after($id, 'data:image/svg+xml;base64,');                 // not reported
        preg_match_all('/data-name="([^"]*)"/', $html);                // reported
        preg_match('/Nachricht \d+/', $html);                          // not reported
        str_contains($html, '<article');                               // reported
        mb_substr_count($html, 'bg-blue-500" data-dot');               // reported
        mb_strpos($html, 'Review');                                   // text: not reported
        $expectation->toContain('[data-name="Ben"]');                  // a CSS selector: not reported
    }
}
