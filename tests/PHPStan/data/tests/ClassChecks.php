<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPStan\data\tests;

use DOMElement;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

/**
 * Analysed by ClassAsStringRuleTest, never run. The comment at the end of each line names the
 * call that the rule reports there, or says that it reports nothing.
 */
final class ClassChecks extends TestCase
{
    /** @param array<string, string> $map */
    public function forms(string $html, array $map, int $n): void
    {
        expect($html)->toContain('max-w-[90rem]');                        // toContain() reported: a utility with [ ]
        expect($html)->toContain('lg:grid-cols-4');                       // toContain() reported: a utility after a variant
        expect($html)->toContain('hover:underline');                      // toContain() reported: a known word after a variant
        expect($html)->toContain('md:-mt-4');                             // toContain() reported: a negative utility
        expect($html)->toContain('hover:bg-black/50');                    // toContain() reported: an opacity
        expect($html)->toContain('lg:!mt-4 lg:flex!');                    // toContain() reported: important, before and after
        expect($html)->toContain('group-hover:underline');                // toContain() reported: a group of variants
        expect($html)->toContain('group-hover/item:underline');           // toContain() reported: a named group
        expect($html)->toContain('peer-checked/name:block');              // toContain() reported: a named peer
        expect($html)->toContain('focus-within:ring-2');                  // toContain() reported
        expect($html)->toContain('aria-checked:bg-blue-500');             // toContain() reported
        expect($html)->toContain('data-active:flex');                     // not reported: a data-* name, for html.markupAsString
        expect($html)->toContain('data-[state=open]:block');              // toContain() reported: an arbitrary data variant
        expect($html)->toContain('has-[:checked]:bg-blue-500');           // toContain() reported
        expect($html)->toContain('max-md:hidden');                        // toContain() reported: a breakpoint range
        expect($html)->toContain('@md:flex');                             // toContain() reported: a container query
        expect($html)->toContain('[&>li]:mt-2');                          // toContain() reported: an arbitrary variant
        expect($html)->toContain('*:p-2');                                // toContain() reported: the children
        expect($html)->toContain('nth-3:underline');                      // toContain() reported
        expect($html)->toContain('lg:[mask-type:luminance]');             // toContain() reported: an arbitrary property
        expect($html)->toContain('headerRowInner max-w-[90rem] flex');    // toContain() reported: a list of classes
        expect($html)->toContain("lg:grid-cols-{$n}");                    // toContain() reported: interpolated
        expect($html)->toContain("lg:{$n}");                              // not reported: a variable after a variant is no utility yet
        expect($html)->toContain("after:{$n}");                           // not reported: a rule of validation with a variable
        expect($html)->toContain("{$n}-[{$n}]");                          // not reported: variables in brackets
        expect($html)->toContain('max-w-[90rem] '.$map['x']);             // toContain() reported: concatenated
        expect($html)->toContain('lg:'.'flex');                           // toContain() reported: joined before the check
        expect($html)->toContain('headerNav');                            // toContain() reported with camelCase
        expect($html)->toContain('header-grid');                          // not reported: kebab-case
        expect($html)->toContain('hidden');                               // not reported: a plain word
        expect($html)->toContain('All rights reserved.');                 // not reported: text
        expect($html)->toContain('Projekte flex');                        // not reported: text and a word
        expect($html)->toContain("Projekte {$n}");                        // not reported: text and a variable
        expect($html)->toContain('mailto:info@example.test');             // not reported: a URL scheme
        expect($html)->toContain('https://example.test/lg:x');            // not reported: a URL
        expect($html)->toContain('Leak:');                                // not reported: a word with a colon
        expect($html)->toContain('10:30');                                // not reported: a time
        expect($html)->toContain('C:\temp');                              // not reported: a path
        expect($html)->toContain('after:today');                          // not reported: a rule of validation
        expect($html)->toContain('first:name');                           // not reported: a key and a value
        expect($html)->toContain('aria-label:foo');                       // not reported: no utility after the colon
        expect($html)->toContain('max-width:none');                       // not reported: CSS
        expect($html)->toContain('min-height:auto');                      // not reported: CSS
        expect($html)->toContain('[data-state=open]');                    // not reported: a CSS selector
        expect($html)->toContain('errors-[name]');                        // not reported: a word in brackets is a key
        expect($html)->toContain('--row-bg: #f5f5f5');                    // not reported: a custom property
        expect($html)->toContain('data-x lg:flex');                       // not reported: markup, for html.markupAsString
        expect($html)->toContain($map['lg:flex']);                        // not reported: the key of an array
    }

    /** @param array<string, string> $map */
    public function subjects(string $html, string|false $content, ?string $maybe, mixed $value, DOMElement $element): void
    {
        expect(['x'])->toBeArray()->and($html)->toContain('sm:flex');     // toContain() reported: the value of and()
        expect([$html])->each->toContain('md:flex');                      // toContain() reported: one element after each
        expect($content)->toContain('xl:flex');                           // toContain() reported: string|false
        expect($maybe)->toContain('xl:flex');                             // toContain() reported: ?string
        expect($html)->when(true, fn ($e) => $e)->toContain('lg:flex');   // toContain() reported: when() keeps the value
        expect($html)
            ->toContain('Review')
            ->toContain('hover:underline');                               // toContain() reported: on the line of the check
        expect($html)->not->toContain('lg:grid-cols-4');                  // not reported: negated
        expect($html)->toBe('lg:flex');                                   // not reported: equality
        expect(['lg:flex'])->toContain('lg:flex');                        // not reported: an array
        expect($value)->toContain('lg:flex');                             // not reported: mixed
        expect($html)->json()->toContain('lg:flex');                      // not reported: the chain changes the value
        expect($element->getAttribute('class'))->toContain('lg:flex');    // not reported: the value of a class attribute
    }

    public function checks(string $html): void
    {
        expect($html)->toStartWith('lg:flex');                            // toStartWith() reported
        expect($html)->toEndWith('lg:flex');                              // toEndWith() reported
        expect($html)->toMatch('/lg:flex/');                              // toMatch() reported: the body of the pattern
        expect($html)->toMatch('~max-w-\[90rem\]~');                      // toMatch() reported: escapes removed
        expect($html)->toMatch('/lg:grid-cols-\d+/');                     // not reported: a real pattern
        expect($html)->toMatch('/item-[0-9]/');                           // not reported: a character class
        expect($html)->toStartWith('Review', 'lg:flex');                  // not reported: the message
        $this->assertStringContainsString('lg:flex', $html);              // assertStringContainsString() reported
        self::assertStringContainsString('lg:flex', $html);               // assertStringContainsString() reported
        $this->assertStringContainsStringIgnoringCase('lg:flex', $html);  // assertStringContainsStringIgnoringCase() reported
        $this->assertStringContainsStringIgnoringLineEndings('lg:flex', $html); // assertStringContainsStringIgnoringLineEndings() reported
        $this->assertStringStartsWith('lg:flex', $html);                  // assertStringStartsWith() reported
        $this->assertStringEndsWith('lg:flex', $html);                    // assertStringEndsWith() reported
        $this->assertMatchesRegularExpression('#lg:flex#i', $html);       // assertMatchesRegularExpression() reported
        $this->assertStringContainsString(haystack: $html, needle: 'lg:flex'); // assertStringContainsString() reported: named
        $this->assertStringContainsString(haystack: 'sm:flex', needle: $html); // not reported: the literal is the haystack
        $this->assertStringContainsString('Review', $html, 'lg:flex');    // not reported: the message
        $this->assertStringNotContainsString('lg:flex', $html);           // not reported: negative
        $this->assertStringNotContainsStringIgnoringCase('lg:flex', $html); // not reported: negative
        $this->assertStringStartsNotWith('lg:flex', $html);               // not reported: negative
        $this->assertStringEndsNotWith('lg:flex', $html);                 // not reported: negative
        $this->assertDoesNotMatchRegularExpression('/lg:flex/', $html);   // not reported: negative
    }

    public function reads(string $html, Stringable $text, ?Stringable $maybe, Collection $items, DOMElement $element): void
    {
        expect(str_contains($html, 'lg:flex'))->toBeTrue();               // str_contains() reported: found
        expect(str_contains(needle: 'lg:flex', haystack: $html))->toBeTrue(); // str_contains() reported: named
        expect(strpos($html, 'lg:flex'))->not->toBeFalse();               // strpos() reported: not false is found
        expect(stripos($html, 'lg:flex'))->toBeInt();                     // stripos() reported
        expect(substr_count($html, 'lg:flex'))->toBe(2);                  // substr_count() reported: a count
        expect(preg_match('/lg:flex/', $html))->toBe(1);                  // preg_match() reported
        expect(Str::contains($html, ['lg:flex', 'md:flex']))->toBeTrue(); // contains() reported: an array of needles
        expect(Str::startsWith($html, 'lg:flex'))->toBeTrue();            // startsWith() reported
        expect(Str::after($html, 'lg:flex'))->toContain('Review');        // not reported: a cut does not tell found from absent
        expect(Str::between($html, 'lg:flex', 'md:flex'))->toBeString();  // not reported: a cut
        expect(Str::match('/lg:flex/', $html))->not->toBeEmpty();         // match() reported: the pattern comes first
        expect($text->contains('lg:flex'))->toBeTrue();                   // contains() reported: a Stringable
        expect($maybe?->contains('lg:flex'))->toBeTrue();                 // contains() reported: once for ?->
        $this->assertTrue(str_contains($html, 'lg:flex'));                // str_contains() reported
        $this->assertSame(1, preg_match('/lg:flex/', $html));             // preg_match() reported
        $this->assertNotSame(0, substr_count($html, 'lg:flex'));          // substr_count() reported: not zero is found
        $this->assertGreaterThan(0, mb_substr_count($html, 'lg:flex'));   // mb_substr_count() reported
        $this->assertNotFalse(mb_strpos($html, 'lg:flex'));               // mb_strpos() reported
        expect(substr_count($html, 'lg:flex'))->toBeGreaterThan(0);       // substr_count() reported
        expect(strpos($html, 'lg:flex'))->toBe(0);                        // strpos() reported: position 0 is found
        $this->assertSame(0, strpos($html, 'lg:flex'));                   // strpos() reported: position 0 is found
        expect(Str::matchAll('/lg:flex/', $html))->toHaveCount(2);        // matchAll() reported: a collection of matches
        $this->assertCount(1, Str::matchAll('/lg:flex/', $html));         // matchAll() reported
        expect(! str_contains($html, 'lg:flex'))->toBeFalse();            // str_contains() reported: not false is found
        expect(substr_count($html, 'lg:flex'))->toBeLessThan(1);          // not reported: absent
        expect(substr_count($html, 'lg:flex'))->toBeLessThanOrEqual(0);   // not reported: absent
        $this->assertLessThan(1, substr_count($html, 'lg:flex'));         // not reported: absent
        expect(strpos($html, 'lg:flex'))->toBeTruthy();                   // not reported: position 0 is falsy, the check says neither
        expect(! strpos($html, 'lg:flex'))->toBeFalse();                  // not reported: ! on a position says neither
        expect(str_contains($html, 'lg:flex'))->toBeBool()->toBeFalse();  // not reported: toBeBool() makes no claim
        expect(str_contains($html, 'lg:flex'))->and(true)->toBeTrue();    // not reported: and() starts a new value
        expect(str_contains($html, 'lg:flex'))->toBe($n > 0);             // not reported: no literal to compare
        expect(Str::after($html, 'lg:flex'))->toBe($html);                // not reported: a cut
        assertTrue(Str::contains($html, 'lg:flex'));                      // contains() reported: an imported function of PHPUnit
        expect(str_contains($html, 'lg:flex'))->toEqual(true);            // str_contains() reported: loosely true
        $this->assertNotEquals(0, substr_count($html, 'lg:flex'));        // substr_count() reported: loosely not zero
        expect(Str::match('/lg:flex/', $html))->not->toBe('');            // match() reported: some text
        expect(Str::matchAll('/lg:flex/', $html))->not->toHaveCount(0);   // matchAll() reported
        $this->assertEquals(0, strpos($html, 'lg:flex'));                 // not reported: false == 0, the check also passes when absent
        expect(strpos($html, 'lg:flex'))->toEqual(0);                     // not reported: false == 0
        $this->assertGreaterThanOrEqual(0, strpos($html, 'lg:flex'));     // not reported: false >= 0
        expect(substr_count($html, 'lg:flex'))->toEqual(0);               // not reported: absent
        expect(substr_count($html, 'lg:flex'))->not->toBe('');            // not reported: a count is never ''
        expect(Str::of($html)->match('/lg:flex/'))->toBeTruthy();         // not reported: a Stringable is always truthy
        expect(Str::matchAll('/lg:flex/', $html))->toBeGreaterThan(0);    // not reported: a collection is always greater than 0
        expect(str_contains($html, 'lg:flex'))->toBeFalse();              // not reported: absent
        expect(str_contains($html, 'lg:flex'))->not->toBeTrue();          // not reported: absent
        expect(substr_count($html, 'lg:flex'))->toBe(0);                  // not reported: absent
        expect(Str::doesntContain($html, 'lg:flex'))->toBeTrue();         // not reported: a negative search
        $this->assertFalse(str_contains($html, 'lg:flex'));               // not reported: absent
        $this->assertTrue(! str_contains($html, 'lg:flex'));              // not reported: negated
        $this->assertSame(0, substr_count($html, 'lg:flex'));             // not reported: absent
        $this->assertNull(Str::match('/lg:flex/', $html) ?: null);        // not reported: no search in the subject
        expect(Str::matchAll('/x/', 'lg:flex'))->not->toBeEmpty();        // not reported: the literal is the subject
        expect($items->contains('lg:flex'))->toBeTrue();                  // not reported: a collection
        expect(str_contains($element->getAttribute('class'), 'lg:flex'))->toBeTrue(); // not reported: a class attribute
        str_contains($html, 'lg:flex');                                   // not reported: no check
        if (str_contains($html, 'lg:flex')) {                             // not reported: a branch, no check
            $items->filter(fn (string $url): bool => Str::contains($url, 'lg:flex')); // not reported: a filter
        }
    }
}
