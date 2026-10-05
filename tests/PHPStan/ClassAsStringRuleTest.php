<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPStan;

use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SorgeIt\PhpunitPestHtmlAssertions\PHPStan\ClassAsStringRule;

/** @extends RuleTestCase<ClassAsStringRule> */
final class ClassAsStringRuleTest extends RuleTestCase
{
    public const string FIXTURE = __DIR__.'/data/tests/ClassChecks.php';

    public const string TIP = 'When the string is not HTML, add // @phpstan-ignore html.classAsString (<reason>).';

    /**
     * The errors the fixture names: a line that ends in `// name() reported` is reported for that
     * call, `reported with camelCase` only when that form is on.
     *
     * @return list<array{string, int, string}>
     */
    public static function errors(bool $camelCase): array
    {
        $errors = [];

        foreach (file(self::FIXTURE) ?: [] as $index => $line) {
            if (preg_match('~// (\w+)\(\) reported( with camelCase)?~', $line, $match) === 1 && ($camelCase || ! isset($match[2]))) {
                $errors[] = [sprintf('%s() searches a string for a class: ask the element with toHaveSelectorClass() or assertHtmlSelectorClass().', $match[1]), $index + 1, self::TIP];
            }
        }

        return $errors;
    }

    public function test_it_reports_each_search_of_a_string_for_a_class_in_a_test(): void
    {
        $this->analyse([self::FIXTURE], self::errors(camelCase: false));
    }

    public function test_it_leaves_code_outside_the_tests_alone(): void
    {
        $this->analyse([__DIR__.'/../../fixtures/NotATest.php'], []);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new ClassAsStringRule;
    }
}
