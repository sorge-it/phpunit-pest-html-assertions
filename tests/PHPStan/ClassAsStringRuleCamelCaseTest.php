<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPStan;

use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use SorgeIt\PhpunitPestHtmlAssertions\PHPStan\ClassAsStringRule;

/**
 * The rule as `extension.neon` builds it, with `htmlAssertions.camelCaseClasses` on.
 *
 * @extends RuleTestCase<ClassAsStringRule>
 */
final class ClassAsStringRuleCamelCaseTest extends RuleTestCase
{
    /** @return list<string> */
    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__.'/../../extension.neon', __DIR__.'/data/camel-case.neon'];
    }

    public function test_it_reports_a_class_in_camel_case_when_the_parameter_is_on(): void
    {
        $this->analyse([ClassAsStringRuleTest::FIXTURE], ClassAsStringRuleTest::errors(camelCase: true));
    }

    #[Override]
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(ClassAsStringRule::class);
    }
}
