<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPUnit;

use DOMText;
use Illuminate\Testing\TestResponse;
use InvalidArgumentException;
use Nyholm\Psr7\Response as Psr7Response;
use PHPUnit\Framework\TestCase;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\NotOneNode;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Excerpt;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\NotAPage;
use SorgeIt\PhpunitPestHtmlAssertions\Tests\Page;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
        $this->expectException(NotAPage::class);
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

    public function test_a_node_in_a_template_is_not_part_of_the_page(): void
    {
        $html = Html::of('<main><p data-total></p><template><p data-total>42.00 EUR</p><a data-in="template">x</a></template></main>');

        self::assertSame([''], $html->texts('[data-total]'));
        self::assertSame(1, $html->count('template'));
        self::assertSame(0, $html->within('template')->count('p'));
        self::assertNotContains('template', $html->attributeValues());
    }

    public function test_within_stops_where_the_region_is_missing_or_there_twice(): void
    {
        foreach (['[data-here]' => 0, '[data-name]' => 2] as $selector => $count) {
            try {
                Html::of(Page::HTML)->within($selector);
            } catch (NotOneNode $notOneNode) {
                self::assertStringStartsWith(sprintf('(page): within() needs exactly one match. The selector "%s" finds %d nodes.', $selector, $count), $notOneNode->getMessage());

                continue;
            }

            self::fail('within() passed on '.$selector);
        }
    }

    public function test_frame_reads_the_document_in_the_srcdoc(): void
    {
        $mail = Html::of(Page::HTML)->frame('[data-mail]');

        self::assertSame(['Hello'], $mail->texts('[data-mail-text]'));
        self::assertSame('(page) > [data-mail] > srcdoc', $mail->path());
    }

    public function test_frame_stops_on_a_node_without_srcdoc(): void
    {
        $this->expectException(NotAPage::class);
        $this->expectExceptionMessage('The frame (page) > [data-head] has no srcdoc, so it holds no page.');

        Html::of(Page::HTML)->frame('[data-head]');
    }

    public function test_frame_stops_where_the_frame_is_missing(): void
    {
        $this->expectException(NotOneNode::class);
        $this->expectExceptionMessage('(page): frame() needs exactly one match. The selector "iframe[data-here]" finds 0 nodes.');

        Html::of(Page::HTML)->frame('iframe[data-here]');
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

    public function test_the_excerpt_leaves_out_a_comment(): void
    {
        self::assertSame("  <p>\n    Anna", Html::of('<main><p>Anna<!-- the name --></p></main>')->within('p')->excerpt());
    }

    public function test_the_excerpt_cuts_a_long_region(): void
    {
        $long = '<ul>'.str_repeat('<li>Eintrag</li>', 50).'</ul>';

        $excerpt = Html::of($long)->excerpt();

        self::assertCount(41, explode("\n", $excerpt));
        self::assertMatchesRegularExpression('/… \d+ more lines$/', $excerpt);
    }

    public function test_the_excerpt_shows_forty_lines_and_counts_the_rest(): void
    {
        $excerpt = fn (string $items): string => Html::of('<main><ol>'.$items.'</ol></main>')->within('ol')->excerpt();
        $nineteen = "  <ol>\n".str_repeat("    <li>\n      x\n", 19);

        self::assertSame($nineteen.'    y', $excerpt(str_repeat('<li>x</li>', 19).'y'));
        self::assertSame($nineteen."    <li>\n… 1 more lines", $excerpt(str_repeat('<li>x</li>', 20)));
    }

    public function test_the_excerpt_cuts_a_long_text_a_long_value_and_a_long_tag_but_not_one_that_fits(): void
    {
        [$a, $b, $c, $d] = [str_repeat('a', 40), str_repeat('b', 41), str_repeat('c', 100), str_repeat('d', 101)];
        $page = sprintf('<main><div><p title="%s">%s</p><p title="%s" hidden>%s</p><b title="%s" lang="%s" id="x"></b></div></main>', $a, $c, $b, $d, $a, $a);

        self::assertSame(
            sprintf("  <div>\n    <p title=\"%s\">\n      %s\n    <p title=\"%s…\" hidden>\n      %s…\n    <b title=\"%s\" lang=\"%s\"…", $a, $c, mb_substr($b, 2), mb_substr($d, 2), $a, $a),
            Html::of($page)->within('div')->excerpt(),
        );
    }

    public function test_a_response_without_content_reads_as_an_empty_page(): void
    {
        $file = new BinaryFileResponse(__FILE__);

        self::assertSame('', Html::of(new StreamedResponse(fn (): null => null))->text());
        self::assertSame('', Html::of(TestResponse::fromBaseResponse($file))->text());
    }

    public function test_a_region_in_a_region_and_each_match_in_it_keep_the_whole_path(): void
    {
        $body = Html::of(Page::HTML)->within('[data-body]');

        self::assertSame('(page) > [data-body] > form', $body->within('form')->path());
        self::assertSame('(page) > [data-body] > form > input:nth-match(2)', $body->within('form')->matches('input')[1]->path());
    }

    public function test_a_region_counts_as_one_assertion(): void
    {
        $before = self::getCount();

        Html::of(Page::HTML)->within('[data-people]');

        self::assertSame($before + 1, self::getCount());
    }

    public function test_attribute_values_include_those_of_the_element_of_the_region(): void
    {
        self::assertSame(['/out?u=1', ''], Html::of(Page::HTML)->within('[data-link]')->attributeValues());
    }

    public function test_the_matches_are_a_list_also_where_a_template_or_the_region_leaves_out_the_first(): void
    {
        self::assertSame(['b'], Html::of('<main><template><p>a</p></template><p>b</p></main>')->texts('p'));
        self::assertSame(['in'], Html::of('<main><p>out</p><div><p>in</p></div></main>')->within('div')->texts('p'));
    }

    public function test_a_crawler_of_texts_has_no_element_to_stand_on(): void
    {
        $texts = (new Crawler('<main><p>a</p></main>'))->filterXPath('//p/text()');

        self::assertSame([], Html::of($texts)->roots());
    }

    public function test_the_roots_of_a_crawler_are_a_list_also_where_a_text_comes_first(): void
    {
        $nodes = (new Crawler('<main><p>a</p><p>b</p></main>'))->filterXPath('//p[1]/text() | //p[2]');

        self::assertSame([$nodes->getNode(1)], Html::of($nodes)->roots());
    }

    public function test_the_text_of_a_node_leaves_out_what_is_not_utf_8(): void
    {
        $page = Html::of('<main><p data-broken></p></main>');
        $page->elements('[data-broken]')[0]->appendChild(new DOMText("\xFF"));

        self::assertSame([''], $page->texts('[data-broken]'));
    }

    public function test_the_excerpt_collapses_white_space_and_leaves_out_an_empty_text_or_one_that_is_not_utf_8(): void
    {
        self::assertSame('  Anna Example', Excerpt::of([new DOMText("\xFF"), new DOMText(" \n "), new DOMText("  Anna \n Example ")]));
    }
}
