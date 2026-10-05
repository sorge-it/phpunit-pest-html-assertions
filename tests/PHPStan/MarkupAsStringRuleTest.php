<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPStan;

use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SorgeIt\PhpunitPestHtmlAssertions\PHPStan\MarkupAsStringRule;

/** @extends RuleTestCase<MarkupAsStringRule> */
final class MarkupAsStringRuleTest extends RuleTestCase
{
    private const string TIP = 'Ask the DOM with a CSS selector, see the README of sorge-it/phpunit-pest-html-assertions.';

    public function test_it_reports_each_check_of_markup_as_a_string_in_a_test(): void
    {
        $check = fn (string $method, int $line): array => [sprintf('%s() checks markup as a string: its literal holds a tag or an attribute.', $method), $line, self::TIP];
        $read = fn (string $method, int $line): array => [sprintf('%s() reads HTML as a string: its literal holds a tag or an attribute.', $method), $line, self::TIP];

        $this->analyse([__DIR__.'/data/tests/StringChecks.php'], [
            ['assertSeeHtml() checks markup as a string.', 14, self::TIP],
            ['assertDontSeeHtml() checks markup as a string.', 15, self::TIP],
            ['assertSeeInOrder() checks markup as a string.', 16, self::TIP],
            ['assertSeeHtmlInOrder() checks markup as a string.', 17, self::TIP],
            ['assertSee() with escaping off checks markup as a string.', 19, self::TIP],
            ['assertDontSee() with escaping off checks markup as a string.', 20, self::TIP],
            $check('toContain', 21),
            $check('toContain', 22),
            $check('toBe', 23),
            $check('toStartWith', 24),
            $check('toEndWith', 25),
            $check('toMatch', 26),
            $check('toContain', 27),
            $check('toContain', 28),
            $check('assertStringContainsString', 34),
            $check('assertMatchesRegularExpression', 35),
            $read('between', 36),
            $read('after', 37),
            $read('afterLast', 38),
            $read('beforeLast', 39),
            $read('betweenFirst', 40),
            $read('contains', 41),
            $read('between', 42),
            $read('preg_match_all', 44),
            $read('str_contains', 46),
            $read('mb_substr_count', 47),
        ]);
    }

    public function test_it_reads_a_tests_directory_with_a_capital_letter(): void
    {
        $this->analyse([__DIR__.'/../../fixtures/Tests/UpperCaseTest.php'], [
            ['assertSeeHtml() checks markup as a string.', 12, self::TIP],
        ]);
    }

    public function test_it_leaves_code_outside_the_tests_alone(): void
    {
        $this->analyse([__DIR__.'/../../fixtures/NotATest.php'], []);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new MarkupAsStringRule;
    }
}
