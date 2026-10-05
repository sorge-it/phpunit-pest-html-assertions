<?php

declare(strict_types=1);

use Illuminate\Testing\TestResponse;
use Nyholm\Psr7\Response as Psr7Response;
use Pest\Expectation;
use PHPUnit\Framework\ExpectationFailedException;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\NotOneNode;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\Tests\Page;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

use function SorgeIt\PhpunitPestHtmlAssertions\Pest\html;

it('passes where the page holds what the expectation asks for', function (Closure $check): void {
    $check(expect(Page::HTML));
})->with([
    'a node' => fn (Expectation $page): Expectation => $page->toHaveSelector('[data-people]'),
    'no node' => fn (Expectation $page): Expectation => $page->not->toHaveSelector('[data-here]'),
    'a count' => fn (Expectation $page): Expectation => $page->toHaveSelectorCount('[data-name]', 2),
    'a count at least' => fn (Expectation $page): Expectation => $page->toHaveSelectorCountAtLeast('[data-name]', 1),
    'a text' => fn (Expectation $page): Expectation => $page->toHaveSelectorText('[data-name="Anna"]', 'Anna Example'),
    'a part of a text' => fn (Expectation $page): Expectation => $page->toHaveSelectorTextContaining('h1', 'Friday'),
    'a text of some node' => fn (Expectation $page): Expectation => $page->toHaveAnySelectorText('[data-name]', 'Ben'),
    'a part of some text' => fn (Expectation $page): Expectation => $page->toHaveAnySelectorTextContaining('[data-name]', 'Exa'),
    'an attribute' => fn (Expectation $page): Expectation => $page->toHaveSelectorAttribute('[data-link]', 'href', '/out?u=1'),
    'an attribute without a value' => fn (Expectation $page): Expectation => $page->toHaveSelectorAttribute('[data-agree]', 'checked'),
    'a part of an attribute' => fn (Expectation $page): Expectation => $page->toHaveSelectorAttributeContaining('[data-link]', 'href', 'u=1'),
    'an attribute by the start of its name' => fn (Expectation $page): Expectation => $page->toHaveSelectorAttributeNamed('[data-body]', 'wire:poll'),
    'a class' => fn (Expectation $page): Expectation => $page->toHaveSelectorClass('[data-name="Anna"]', 'bg-blue-500'),
    'a part of the text' => fn (Expectation $page): Expectation => $page->toHaveTextContaining('Review confirmed'),
    'a text twice' => fn (Expectation $page): Expectation => $page->toHaveTextCount('Review', 2),
    'an order' => fn (Expectation $page): Expectation => $page->toAppearBefore('[data-head]', '[data-body]'),
    'a title' => fn (Expectation $page): Expectation => $page->toHaveTitle('A page'),
    'a value' => fn (Expectation $page): Expectation => $page->toHaveInputValue('email', 'anna@example.test'),
    'a checked box' => fn (Expectation $page): Expectation => $page->toBeChecked('[data-agree]'),
    'a chosen option' => fn (Expectation $page): Expectation => $page->toHaveSelectedOption('[data-day]', 'fr'),
    'a disabled button' => fn (Expectation $page): Expectation => $page->toBeDisabled('[data-send]'),
    'a link' => fn (Expectation $page): Expectation => $page->toHaveLink('Next', '/out?u=1'),
    'an empty node' => fn (Expectation $page): Expectation => $page->toBeEmptyNode('[data-empty]'),
]);

it('fails where the page does not', function (Closure $check): void {
    expect(fn (): mixed => $check(expect(Page::HTML)))->toThrow(ExpectationFailedException::class);
})->with([
    'no such node' => fn (Expectation $page): Expectation => $page->toHaveSelector('[data-here]'),
    'a node that is there' => fn (Expectation $page): Expectation => $page->not->toHaveSelector('[data-people]'),
    'another count' => fn (Expectation $page): Expectation => $page->toHaveSelectorCount('[data-name]', 3),
    'too few' => fn (Expectation $page): Expectation => $page->toHaveSelectorCountAtLeast('[data-name]', 3),
    'a part not in the text' => fn (Expectation $page): Expectation => $page->toHaveSelectorTextContaining('h1', 'Montag'),
    'a text of no node' => fn (Expectation $page): Expectation => $page->toHaveAnySelectorText('[data-name]', 'Clara'),
    'a part of no text' => fn (Expectation $page): Expectation => $page->toHaveAnySelectorTextContaining('[data-name]', 'Cla'),
    'another attribute value' => fn (Expectation $page): Expectation => $page->toHaveSelectorAttribute('[data-link]', 'href', '/in'),
    'a part not in the attribute' => fn (Expectation $page): Expectation => $page->toHaveSelectorAttributeContaining('[data-link]', 'href', 'u=2'),
    'no attribute of that name' => fn (Expectation $page): Expectation => $page->toHaveSelectorAttributeNamed('[data-head]', 'wire:poll'),
    'a missing class' => fn (Expectation $page): Expectation => $page->toHaveSelectorClass('[data-name="Ben"]', 'bg-blue-500'),
    'another text of the region' => fn (Expectation $page): Expectation => $page->toHaveText('Review'),
    'a part not in the text of the page' => fn (Expectation $page): Expectation => $page->toHaveTextContaining('alert(1)'),
    'a text once' => fn (Expectation $page): Expectation => $page->toHaveTextCount('Review', 1),
    'the other order' => fn (Expectation $page): Expectation => $page->toAppearBefore('[data-body]', '[data-head]'),
    'another title' => fn (Expectation $page): Expectation => $page->toHaveTitle('Another page'),
    'another value' => fn (Expectation $page): Expectation => $page->toHaveInputValue('email', 'ben@example.test'),
    'an unchecked box' => fn (Expectation $page): Expectation => $page->toBeChecked('input[name="email"]'),
    'another option' => fn (Expectation $page): Expectation => $page->toHaveSelectedOption('[data-day]', 'mo'),
    'an enabled button' => fn (Expectation $page): Expectation => $page->toBeDisabled('[data-cancel]'),
    'a link to elsewhere' => fn (Expectation $page): Expectation => $page->toHaveLink('Next', '/in'),
    'a node with text' => fn (Expectation $page): Expectation => $page->toBeEmptyNode('h1'),
]);

it('reads a string, a crawler, a region, a test response and a PSR-7 response', function (mixed $page): void {
    expect($page)->toHaveSelectorCount('[data-name]', 2);
})->with([
    'a string' => [Page::HTML],
    'a crawler' => fn (): Crawler => new Crawler(Page::HTML),
    'a region' => fn (): Html => Html::of(Page::HTML),
    'a test response' => fn (): TestResponse => TestResponse::fromBaseResponse(new Response(Page::HTML)),
    'a response of Symfony' => fn (): Response => new Response(Page::HTML),
    'a PSR-7 response' => fn (): Psr7Response => new Psr7Response(200, [], Page::HTML),
]);

it('narrows the page to a region, and every check after it looks inside it only', function (): void {
    expect(html(Page::HTML))->within('[data-people]')
        ->toHaveText('Anna Example Ben')
        ->not->toHaveSelector('h1')
        ->toHaveSelectorCount('li', 2);
});

it('names the path of the region where a check inside it fails', function (): void {
    expect(fn (): mixed => expect(html(Page::HTML))->within('[data-people]')->toHaveSelector('h1'))
        ->toThrow(ExpectationFailedException::class, '(page) > [data-people] has a node matching "h1"');
});

it('fails within a region that is missing, so a check turned around after it cannot pass on nothing', function (): void {
    expect(fn (): mixed => expect(html(Page::HTML))->within('[data-here]')->not->toHaveSelector('a'))
        ->toThrow(ExpectationFailedException::class, 'has 1 nodes matching "[data-here]"');
});

it('fails a check of one node on none or two, also turned around', function (Closure $check): void {
    expect(fn (): mixed => $check(expect(Page::HTML)))->toThrow(NotOneNode::class, 'a check of one node needs exactly one match.');
})->with([
    'two nodes' => fn (Expectation $page): Expectation => $page->toHaveSelectorText('[data-name]', 'Anna Example'),
    'two nodes, turned around' => fn (Expectation $page): Expectation => $page->not->toHaveSelectorText('[data-name]', 'Clara'),
    'no node, turned around' => fn (Expectation $page): Expectation => $page->not->toHaveSelectorClass('[data-here]', 'x'),
]);

it('reads the document of a frame', function (): void {
    expect(html(Page::HTML))->frame('[data-mail]')->toHaveSelectorText('[data-mail-text]', 'Hello');
});

it('runs the same checks on every match, and fails where nothing matches', function (): void {
    $seen = [];

    expect(Page::HTML)->eachMatch('[data-name]', function (Expectation $name, int $index) use (&$seen): void {
        $name->toHaveSelectorClass(':scope', 'rounded-lg');
        $seen[] = $index;
    });

    expect($seen)->toBe([0, 1])
        ->and(fn (): Expectation => expect(Page::HTML)->eachMatch('[data-here]', fn (): null => null))
        ->toThrow(ExpectationFailedException::class, 'has a node matching "[data-here]"');
});

it('reads texts, raw texts and attributes as lists that Pest checks further', function (): void {
    $page = html(Page::HTML);

    expect($page)->texts('[data-name]')->toBe(['Anna Example', 'Ben'])
        ->and($page)->rawTexts('[data-text]')->toBe(["Line one\nLine two"])
        ->and($page)->attributes('[data-name]', 'data-name')->toBe(['Anna', 'Ben']);

    expect(fn (): mixed => expect($page)->texts('[data-name]')->toBe(['Anna']))->toThrow(ExpectationFailedException::class);
});

it('shows the path and the region where Pest dumps it', function (): void {
    expect(print_r(html(Page::HTML)->within('[data-people]'), true))
        ->toContain('(page) > [data-people]')
        ->toContain('Anna Example');
});
