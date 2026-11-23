<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\Rector;

use Iterator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Application\ApplicationFileProcessor;
use Rector\Configuration\ConfigurationFactory;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\Testing\Fixture\FixtureSplitter;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use SorgeIt\PhpunitPestHtmlAssertions\Rector\CrawlerToHtmlRector;

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

    /**
     * Pest defines no `PHPUNIT_COMPOSER_INSTALL`, so Rector keeps an error of the rule as a system
     * error and leaves the file as it was: a fixture without a change would pass on a crash.
     */
    #[DataProvider('provideData')]
    public function test_it_reads_each_fixture_without_an_error(string $file): void
    {
        $input = sprintf('%s/%s.errors.php', dirname($file), basename($file, '.php.inc'));
        file_put_contents($input, FixtureSplitter::split($file)[0]);

        try {
            SimpleParameterProvider::setParameter(Option::SOURCE, [$input]);
            $this->make(DynamicSourceLocatorProvider::class)->setFilePath($input);
            $result = $this->make(ApplicationFileProcessor::class)->processFiles([$input], $this->make(ConfigurationFactory::class)->createForTests([$input]));
        } finally {
            unlink($input);
        }

        self::assertSame([], $result->getSystemErrors());
    }

    public function test_its_definition_shows_the_texts_of_the_matches_rewritten(): void
    {
        $definition = (new CrawlerToHtmlRector)->getRuleDefinition();

        self::assertSame('Rewrites crawler code to the tools of Html', $definition->getDescription());
        self::assertCount(1, $definition->getCodeSamples());
        self::assertSame("new Crawler(\$html)->filter('li')->each(fn (Crawler \$node): string => \$node->text());", $definition->getCodeSamples()[0]->getBadCode());
        self::assertSame("Html::of(\$html)->texts('li');", $definition->getCodeSamples()[0]->getGoodCode());
    }

    #[Override]
    public function provideConfigFilePath(): string
    {
        return __DIR__.'/config.php';
    }
}
