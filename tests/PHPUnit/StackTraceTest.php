<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPUnit;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\ExcludeList;
use PHPUnit\Util\Filter;
use ReflectionProperty;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\AssertsHtml;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\StackTrace;

/**
 * The trace PHPUnit prints; Pest shows its first frame. Each test restores the
 * excluded directories: Pest runs the suite and cannot run a test in a process of its own.
 */
final class StackTraceTest extends TestCase
{
    use AssertsHtml;

    /** @var list<string> */
    private array $excluded;

    protected function setUp(): void
    {
        // Filled once, with PHPUnit's own directories: restored unfilled, it would stay empty.
        $this->excluded = (new ExcludeList())->getExcludedDirectories();
    }

    protected function tearDown(): void
    {
        $this->directories()->setValue(null, $this->excluded);
    }

    public function test_the_suite_of_the_package_runs_with_its_frames(): void
    {
        self::assertFalse((new ExcludeList())->isExcluded($this->package().'/src/PHPUnit/Assertion.php'));
    }

    public function test_a_failure_in_another_project_points_at_the_test(): void
    {
        StackTrace::hide(sys_get_temp_dir(), false);

        $trace = Filter::stackTraceFromThrowableAsString($this->failure());

        self::assertStringStartsWith(__FILE__.':', $trace);
        self::assertStringNotContainsString($this->package().'/src/', $trace);
    }

    public function test_a_root_without_a_real_path_counts_as_another_project(): void
    {
        StackTrace::hide('/no/such/project', false);

        self::assertStringStartsWith(__FILE__.':', Filter::stackTraceFromThrowableAsString($this->failure()));
    }

    public function test_the_checkout_of_the_package_keeps_its_frames(): void
    {
        StackTrace::hide($this->package().'/tests/..', false);

        $trace = Filter::stackTraceFromThrowableAsString($this->failure());

        self::assertStringStartsWith($this->package().'/src/PHPUnit/Assertion.php:', $trace);
    }

    public function test_a_bug_report_keeps_the_frames_of_the_package(): void
    {
        StackTrace::hide(sys_get_temp_dir(), true);

        $trace = Filter::stackTraceFromThrowableAsString($this->failure());

        self::assertStringStartsWith($this->package().'/src/PHPUnit/Assertion.php:', $trace);
    }

    private function package(): string
    {
        return dirname(__DIR__, 2);
    }

    private function failure(): ExpectationFailedException
    {
        try {
            self::assertHtmlSelectorExists('<p></p>', '[data-missing]');
        } catch (ExpectationFailedException $expectationFailedException) {
            return $expectationFailedException;
        }

        self::fail('The check passed.');
    }

    private function directories(): ReflectionProperty
    {
        return new ReflectionProperty(ExcludeList::class, 'directories');
    }
}
