<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\Rector;

use Iterator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CrawlerToHtmlRectorTest extends AbstractRectorTestCase
{
    /** @return Iterator<array<int, string>> */
    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__.'/Fixture');
    }

    #[DataProvider('provideData')]
    public function test_it_rewrites_the_mechanical_forms_and_leaves_the_rest(string $file): void
    {
        $this->doTestFile($file);
    }

    #[Override]
    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config.php';
    }
}
