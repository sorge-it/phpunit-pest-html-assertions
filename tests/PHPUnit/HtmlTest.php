<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPUnit;

use Illuminate\Testing\TestResponse;
use InvalidArgumentException;
use Nyholm\Psr7\Response as Psr7Response;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\Tests\Page;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class HtmlTest extends TestCase
{
    public function test_it_reads_each_kind_it_names(): void
    {
        $values = [
            Page::HTML,
            new Crawler(Page::HTML),
            TestResponse::fromBaseResponse(new Response(Page::HTML)),
            TestResponse::fromBaseResponse(new StreamedResponse(function (): void {
                echo Page::HTML;
            })),
            new PageTestView,
            new PageTestComponent,
            new Response(Page::HTML),
            new Psr7Response(200, [], Page::HTML),
        ];

        foreach ($values as $value) {
            self::assertSame(['Anna', 'Ben'], Html::of($value)->attributes('[data-name]', 'data-name'), get_debug_type($value));
        }
    }

    public function test_it_names_the_kinds_it_reads_where_it_gets_another(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('a string, an Html, a Crawler, a TestResponse, a TestView, a TestComponent, a Livewire Testable, a PSR-7 response or a response of Symfony, not int');

        Html::of(42);
    }

    public function test_within_narrows_the_page_to_its_one_region_and_keeps_the_path(): void
    {
        $people = Html::of(Page::HTML)->within('[data-people]');

        self::assertSame('(page) > [data-people]', $people->path());
        self::assertSame(['Anna Example', 'Ben'], $people->texts('li'));
        self::assertSame(0, $people->count('h1'));
    }

    public function test_a_region_finds_what_lies_below_its_element_but_not_the_element_itself(): void
    {
        $people = Html::of(Page::HTML)->within('[data-people]');

        self::assertSame(0, $people->count('[data-people]'));
        self::assertSame(0, $people->count('body ul'));
        self::assertSame(2, $people->count('body li'));
    }

    public function test_scope_alone_names_the_element_of_a_region(): void
    {
        $anna = Html::of(Page::HTML)->within('[data-name="Anna"]');

        self::assertSame(['Anna'], $anna->attributes(':scope', 'data-name'));
    }

    public function test_scope_in_a_longer_selector_or_on_a_whole_page_is_refused(): void
    {
        foreach ([[Html::of(Page::HTML)->within('[data-people]'), ':scope > li'], [Html::of(Page::HTML), ':scope']] as [$html, $selector]) {
            try {
                $html->count($selector);
            } catch (InvalidArgumentException) {
                continue;
            }

            self::fail('count() took '.$selector);
        }

        $this->addToAssertionCount(1);
    }

    public function test_attribute_values_lists_every_value_in_the_region(): void
    {
        $values = Html::of(Page::HTML)->within('[data-people]')->attributeValues();

        self::assertContains('Anna', $values);
        self::assertContains('rounded-lg bg-blue-500', $values);
        self::assertNotContains('/out?u=1', $values);
    }

    public function test_within_fails_where_the_region_is_missing_or_there_twice(): void
    {
        foreach (['[data-here]', '[data-name]'] as $selector) {
            try {
                Html::of(Page::HTML)->within($selector);
            } catch (ExpectationFailedException) {
                continue;
            }

            self::fail('within() passed on '.$selector);
        }

        $this->addToAssertionCount(1);
    }

    public function test_frame_reads_the_document_in_the_srcdoc(): void
    {
        $mail = Html::of(Page::HTML)->frame('[data-mail]');

        self::assertSame(['Hello'], $mail->texts('[data-mail-text]'));
        self::assertSame('(page) > [data-mail] > srcdoc', $mail->path());
    }

    public function test_frame_fails_on_a_node_without_srcdoc(): void
    {
        $this->expectException(AssertionFailedError::class);

        Html::of(Page::HTML)->frame('[data-head]');
    }

    public function test_it_reads_texts_raw_texts_and_attributes_in_the_order_of_the_page(): void
    {
        $page = Html::of(Page::HTML);

        self::assertSame(['Anna Example', 'Ben'], $page->texts('[data-name]'));
        self::assertSame(["Line one\nLine two"], $page->rawTexts('[data-text]'));
        self::assertSame(['rounded-lg bg-blue-500', 'rounded-lg'], $page->attributes('[data-name]', 'class'));
        self::assertSame([null], $page->attributes('[data-head]', 'class'));
    }

    public function test_the_text_of_a_region_leaves_out_scripts_and_styles_but_a_script_asked_for_keeps_its_own(): void
    {
        $page = Html::of(Page::HTML);

        self::assertSame('Review confirmed', $page->within('[data-note]')->text());
        self::assertStringNotContainsString('color: red', $page->text());
        self::assertSame(['alert(1)'], $page->texts('script'));
    }

    public function test_matches_gives_each_node_as_a_region_of_its_own(): void
    {
        $names = Html::of(Page::HTML)->matches('[data-name]');

        self::assertCount(2, $names);
        self::assertSame('Ben', $names[1]->text());
        self::assertSame('(page) > [data-name]:nth-match(2)', $names[1]->path());
    }

    public function test_dump_shows_the_path_and_the_indented_region(): void
    {
        $shown = Html::of(Page::HTML)->within('[data-people]')->__debugInfo();

        self::assertSame('(page) > [data-people]', $shown['path']);
        // @phpstan-ignore html.markupAsString (the dump shows the region as markup)
        self::assertStringContainsString("  <ul data-people>\n    <li data-name=\"Anna\" class=\"rounded-lg bg-blue-500\">\n      Anna Example", $shown['html']);
    }

    public function test_the_excerpt_cuts_a_long_region(): void
    {
        $long = '<ul>'.str_repeat('<li>Eintrag</li>', 50).'</ul>';

        $excerpt = Html::of($long)->excerpt();

        self::assertCount(41, explode("\n", $excerpt));
        self::assertMatchesRegularExpression('/… \d+ more lines$/', $excerpt);
    }
}
