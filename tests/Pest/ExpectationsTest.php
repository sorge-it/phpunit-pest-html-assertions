<?php

declare(strict_types=1);

use Illuminate\Testing\TestResponse;
use Nyholm\Psr7\Response as Psr7Response;
use Pest\Expectation;
use PHPUnit\Framework\ExpectationFailedException;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\NotOneNode;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\NoMatch;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\NotAPage;
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

dataset('failing expectations', [
    'no such node' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveSelector('[data-here]', $message),
    'another count' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveSelectorCount('[data-name]', 3, $message),
    'too few' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveSelectorCountAtLeast('[data-name]', 3, $message),
    'a part not in the text' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveSelectorTextContaining('h1', 'Montag', $message),
    'another text' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveSelectorText('h1', 'Review', $message),
    'a text of no node' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveAnySelectorText('[data-name]', 'Clara', $message),
    'a part of no text' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveAnySelectorTextContaining('[data-name]', 'Cla', $message),
    'another attribute value' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveSelectorAttribute('[data-link]', 'href', '/in', $message),
    'a part not in the attribute' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveSelectorAttributeContaining('[data-link]', 'href', 'u=2', $message),
    'no attribute of that name' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveSelectorAttributeNamed('[data-head]', 'wire:poll', $message),
    'a missing class' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveSelectorClass('[data-name="Ben"]', 'bg-blue-500', $message),
    'another text of the region' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveText('Review', $message),
    'a part not in the text of the page' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveTextContaining('alert(1)', $message),
    'a text once' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveTextCount('Review', 1, $message),
    'the other order' => fn (Expectation $page, string $message = ''): Expectation => $page->toAppearBefore('[data-body]', '[data-head]', $message),
    'another title' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveTitle('Another page', $message),
    'another value' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveInputValue('email', 'ben@example.test', $message),
    'an unchecked box' => fn (Expectation $page, string $message = ''): Expectation => $page->toBeChecked('input[name="email"]', $message),
    'another option' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveSelectedOption('[data-day]', 'mo', $message),
    'an enabled button' => fn (Expectation $page, string $message = ''): Expectation => $page->toBeDisabled('[data-cancel]', $message),
    'a link to elsewhere' => fn (Expectation $page, string $message = ''): Expectation => $page->toHaveLink('Next', '/in', $message),
    'a node with text' => fn (Expectation $page, string $message = ''): Expectation => $page->toBeEmptyNode('h1', $message),
]);

it('fails where the page does not', function (Closure $check): void {
    expect(fn (): mixed => $check(expect(Page::HTML)))->toThrow(ExpectationFailedException::class);
})->with('failing expectations');

it('fails where a node is there that a check turned around asks to miss', function (): void {
    expect(fn (): mixed => expect(Page::HTML)->not->toHaveSelector('[data-people]'))->toThrow(ExpectationFailedException::class);
});

it('shows the message of the test before its own', function (Closure $check): void {
    try {
        $check(expect(Page::HTML), 'The cart shows what the customer chose.');
    } catch (ExpectationFailedException $expectationFailedException) {
        expect($expectationFailedException->getMessage())->toStartWith("The cart shows what the customer chose.\nFailed asserting that (page");

        return;
    }

    $this->fail('The check passed.');
})->with('failing expectations');

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

it('stops at a region that is missing, also turned around, so no check after it can pass on nothing', function (Closure $check, string $exception, string $text): void {
    expect(fn (): mixed => $check(expect(html(Page::HTML))))->toThrow($exception, $text);
})->with([
    'within' => [fn (Expectation $page): mixed => $page->within('[data-here]')->not->toHaveSelector('a'), NotOneNode::class, '(page): within() needs exactly one match. The selector "[data-here]" finds 0 nodes.'],
    'within, turned around' => [fn (Expectation $page): mixed => $page->not->within('[data-here]'), NotOneNode::class, 'within() needs exactly one match.'],
    'frame, turned around' => [fn (Expectation $page): mixed => $page->not->frame('iframe[data-here]'), NotOneNode::class, 'frame() needs exactly one match.'],
    'a frame without srcdoc, turned around' => [fn (Expectation $page): mixed => $page->not->frame('[data-head]'), NotAPage::class, 'has no srcdoc, so it holds no page.'],
    'eachMatch, turned around' => [fn (Expectation $page): mixed => $page->not->eachMatch('[data-here]', fn (): null => null), LogicException::class, 'eachMatch() cannot be turned around'],
]);

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

it('refuses eachMatch turned around, which would pass where one match fails', function (): void {
    expect(fn (): mixed => expect(Page::HTML)->not->eachMatch('[data-name]', fn (Expectation $name): Expectation => $name->toHaveSelectorClass(':scope', 'bg-blue-500')))
        ->toThrow(LogicException::class, 'eachMatch() cannot be turned around: ->not would pass where one match fails.')
        ->and(fn (): mixed => expect(html(Page::HTML))->within('[data-people]')->not->eachMatch('li', fn (): null => null))
        ->toThrow(LogicException::class, 'eachMatch() cannot be turned around');
});

it('runs eachMatch where a test turns around something else around it', function (Closure $check): void {
    expect($check)->not->toThrow(LogicException::class);
})->with([
    // Pest hands a closure to a parameter of the type Closure as it is, without calling it.
    'a closure of the test' => [fn (): Expectation => expect(Page::HTML)->eachMatch('[data-name]', fn (Expectation $name): Expectation => $name->toHaveSelectorClass(':scope', 'rounded-lg'))],
    'a function of the test' => [eachMatchOnEveryName(...)],
]);

function eachMatchOnEveryName(): Expectation
{
    return expect(Page::HTML)->eachMatch('[data-name]', fn (Expectation $name): Expectation => $name->toHaveSelectorClass(':scope', 'rounded-lg'));
}

it('runs the same checks on every match, and stops where nothing matches', function (): void {
    $seen = [];

    expect(Page::HTML)->eachMatch('[data-name]', function (Expectation $name, int $index) use (&$seen): void {
        $name->toHaveSelectorClass(':scope', 'rounded-lg');
        $seen[] = $index;
    });

    expect($seen)->toBe([0, 1])
        ->and(fn (): Expectation => expect(Page::HTML)->eachMatch('[data-here]', fn (): null => null))
        ->toThrow(NoMatch::class, '(page): eachMatch() needs at least one match. The selector "[data-here]" finds 0 nodes.');
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

it('takes the message by name after an optional value', function (): void {
    expect(fn (): mixed => expect(Page::HTML)->toHaveSelectorAttribute('[data-cancel]', 'disabled', message: 'Cancel stays open.'))
        ->toThrow(ExpectationFailedException::class, "Cancel stays open.\nFailed asserting that (page) has one node matching \"[data-cancel]\" with the attribute \"disabled\"");
});

it('shows the message of the test before a check of one node that finds two, also turned around', function (Closure $check): void {
    try {
        $check(expect(Page::HTML));
    } catch (NotOneNode $notOneNode) {
        expect($notOneNode->getMessage())->toStartWith("The list names one person.\n(page): a check of one node needs exactly one match.");

        return;
    }

    $this->fail('The check passed on two nodes.');
})->with([
    'the check' => fn (Expectation $page): Expectation => $page->toHaveSelectorText('[data-name]', 'Anna Example', 'The list names one person.'),
    'turned around' => fn (Expectation $page): Expectation => $page->not->toHaveSelectorText('[data-name]', 'Clara', 'The list names one person.'),
]);

it('shows the message of the test where eachMatch finds nothing or reads no page', function (): void {
    expect(fn (): mixed => expect(Page::HTML)->eachMatch('[data-here]', fn (): null => null, 'Each person has an avatar.'))
        ->toThrow(NoMatch::class, "Each person has an avatar.\n(page): eachMatch() needs at least one match.")
        ->and(fn (): mixed => expect(42)->eachMatch('p', fn (): null => null, 'Each person has an avatar.'))
        ->toThrow(NotAPage::class, "Each person has an avatar.\nA check of HTML reads ");
});

it('stops a check turned around on a value that is not a page, so it cannot pass', function (): void {
    expect(fn (): mixed => expect(['errors' => []])->not->toHaveSelector('[data-errors]'))
        ->toThrow(NotAPage::class, 'A check of HTML reads ');
});

it('keeps the text of a failure without a message', function (): void {
    try {
        // A whole page: the parsers of Symfony 7.4 and 8.1 add a missing head differently.
        expect('<html><head></head><body><ul><li>a</li></ul></body></html>')->toHaveSelectorCount('li', 2);
    } catch (ExpectationFailedException $expectationFailedException) {
        expect($expectationFailedException->getMessage())
            ->toBe("Failed asserting that (page) has 2 nodes matching \"li\".\nThe selector \"li\" finds 1 nodes.\n\nIn (page):\n  <html>\n    <head>\n    <body>\n      <ul>\n        <li>\n          a");

        return;
    }

    $this->fail('The check passed.');
});

// Pest builds the message of `->not` itself: the message of the test is its last argument, without the region.
it('shows the message of the test among the arguments of a check turned around', function (Closure $check, string $text): void {
    expect(fn (): mixed => $check(expect(Page::HTML)))->toThrow(ExpectationFailedException::class, $text);
})->with([
    'by position' => [
        fn (Expectation $page): Expectation => $page->not->toHaveSelector('[data-people]', 'The cart is empty after checkout.'),
        "not to have selector '[data-people]' 'The cart is empty after checkout.'.",
    ],
    'by name' => [
        fn (Expectation $page): Expectation => $page->not->toHaveSelectorAttribute('[data-agree]', 'checked', message: 'The box starts empty.'),
        "not to have selector attribute '[data-agree]' 'checked' 'The box starts empty.'.",
    ],
]);

it('shows the message and the region where a count of zero says that a node is missing', function (): void {
    expect(fn (): mixed => expect(Page::HTML)->toHaveSelectorCount('[data-people]', 0, 'The cart is empty after checkout.'))
        ->toThrow(ExpectationFailedException::class, "The cart is empty after checkout.\nFailed asserting that (page) has 0 nodes matching \"[data-people]\".");
});
