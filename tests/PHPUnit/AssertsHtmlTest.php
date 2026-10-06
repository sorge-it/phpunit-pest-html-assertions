<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests\PHPUnit;

use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\AssertsHtml;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\ChecksARegion;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelector;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorClass;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorText;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\HasSelectorTextContaining;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\IsDisabled;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\Not;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\NotOneNode;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\RegionCheck;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\NotAPage;
use SorgeIt\PhpunitPestHtmlAssertions\Tests\Page;

/** Each check of the trait once green and once red, on the same page. */
final class AssertsHtmlTest extends TestCase
{
    use AssertsHtml;

    /** @return iterable<string, array{Closure(string): void}> */
    public static function passing(): iterable
    {
        yield 'a node' => [fn (string $page) => self::assertHtmlSelectorExists($page, '[data-people]')];
        yield 'no node' => [fn (string $page) => self::assertHtmlSelectorNotExists($page, '[data-here]')];
        yield 'a count' => [fn (string $page) => self::assertHtmlSelectorCount($page, '[data-name]', 2)];
        yield 'a count at least' => [fn (string $page) => self::assertHtmlSelectorCountAtLeast($page, '[data-name]', 2)];
        yield 'a text, collapsed' => [fn (string $page) => self::assertHtmlSelectorTextSame($page, '[data-name="Anna"]', 'Anna Example')];
        yield 'a part of a text' => [fn (string $page) => self::assertHtmlSelectorTextContains($page, 'h1', 'Friday')];
        yield 'a text of some node' => [fn (string $page) => self::assertHtmlAnySelectorTextSame($page, '[data-name]', 'Ben')];
        yield 'a part of some text' => [fn (string $page) => self::assertHtmlAnySelectorTextContains($page, '[data-name]', 'Exa')];
        yield 'an attribute' => [fn (string $page) => self::assertHtmlSelectorAttribute($page, '[data-link]', 'href', '/out?u=1')];
        yield 'an attribute without a value' => [fn (string $page) => self::assertHtmlSelectorAttribute($page, '[data-agree]', 'checked')];
        yield 'a part of an attribute' => [fn (string $page) => self::assertHtmlSelectorAttributeContains($page, '[data-link]', 'href', 'u=1')];
        yield 'an attribute by the start of its name' => [fn (string $page) => self::assertHtmlSelectorAttributeNamed($page, '[data-body]', 'wire:poll')];
        yield 'classes in another order' => [fn (string $page) => self::assertHtmlSelectorClass($page, '[data-name="Anna"]', 'bg-blue-500 rounded-lg')];
        yield 'the text of a region' => [fn (string $page) => self::assertHtmlTextSame(Html::of($page)->within('[data-people]'), 'Anna Example Ben')];
        yield 'a part of the text' => [fn (string $page) => self::assertHtmlTextContains($page, 'Review confirmed')];
        yield 'a text twice' => [fn (string $page) => self::assertHtmlTextCount($page, 'Review', 2)];
        yield 'an order' => [fn (string $page) => self::assertHtmlSelectorBefore($page, '[data-head]', '[data-body]')];
        yield 'a title' => [fn (string $page) => self::assertHtmlPageTitleSame($page, 'A page')];
        yield 'the value of an input' => [fn (string $page) => self::assertHtmlInputValueSame($page, 'email', 'anna@example.test')];
        yield 'the value of a textarea' => [fn (string $page) => self::assertHtmlInputValueSame($page, 'note', 'Please check the café')];
        yield 'the value of a select' => [fn (string $page) => self::assertHtmlInputValueSame($page, 'day', 'fr')];
        yield 'a checked box' => [fn (string $page) => self::assertHtmlCheckboxChecked($page, '[data-agree]')];
        yield 'a chosen option' => [fn (string $page) => self::assertHtmlSelectedOption($page, '[data-day]', 'fr')];
        yield 'disabled by its fieldset' => [fn (string $page) => self::assertHtmlSelectorDisabled($page, '[data-send]')];
        yield 'a link' => [fn (string $page) => self::assertHtmlLink($page, 'Next', '/out?u=1')];
        yield 'an empty node' => [fn (string $page) => self::assertHtmlSelectorEmpty($page, '[data-empty]')];
    }

    /** @return iterable<string, array{Closure(string, string=): void}> */
    public static function failing(): iterable
    {
        yield 'no such node' => [fn (string $page, string $message = '') => self::assertHtmlSelectorExists($page, '[data-here]', $message)];
        yield 'a node that is there' => [fn (string $page, string $message = '') => self::assertHtmlSelectorNotExists($page, '[data-people]', $message)];
        yield 'another count' => [fn (string $page, string $message = '') => self::assertHtmlSelectorCount($page, '[data-name]', 3, $message)];
        yield 'too few' => [fn (string $page, string $message = '') => self::assertHtmlSelectorCountAtLeast($page, '[data-name]', 3, $message)];
        yield 'another text' => [fn (string $page, string $message = '') => self::assertHtmlSelectorTextSame($page, 'h1', 'Review', $message)];
        yield 'a part not in the text' => [fn (string $page, string $message = '') => self::assertHtmlSelectorTextContains($page, 'h1', 'Montag', $message)];
        yield 'a text of no node' => [fn (string $page, string $message = '') => self::assertHtmlAnySelectorTextSame($page, '[data-name]', 'Clara', $message)];
        yield 'a part of no text' => [fn (string $page, string $message = '') => self::assertHtmlAnySelectorTextContains($page, '[data-name]', 'Cla', $message)];
        yield 'another attribute value' => [fn (string $page, string $message = '') => self::assertHtmlSelectorAttribute($page, '[data-link]', 'href', '/in', $message)];
        yield 'a missing attribute' => [fn (string $page, string $message = '') => self::assertHtmlSelectorAttribute($page, '[data-cancel]', 'disabled', message: $message)];
        yield 'a part not in the attribute' => [fn (string $page, string $message = '') => self::assertHtmlSelectorAttributeContains($page, '[data-link]', 'href', 'u=2', $message)];
        yield 'no attribute of that name' => [fn (string $page, string $message = '') => self::assertHtmlSelectorAttributeNamed($page, '[data-head]', 'wire:poll', $message)];
        yield 'a missing class' => [fn (string $page, string $message = '') => self::assertHtmlSelectorClass($page, '[data-name="Ben"]', 'bg-blue-500', $message)];
        yield 'another text of a region' => [fn (string $page, string $message = '') => self::assertHtmlTextSame(Html::of($page)->within('[data-people]'), 'Anna Ben', $message)];
        // A script is no text a reader gets.
        yield 'the text of a script' => [fn (string $page, string $message = '') => self::assertHtmlTextContains($page, 'alert(1)', $message)];
        yield 'a text once' => [fn (string $page, string $message = '') => self::assertHtmlTextCount($page, 'Review', 1, $message)];
        yield 'the other order' => [fn (string $page, string $message = '') => self::assertHtmlSelectorBefore($page, '[data-body]', '[data-head]', $message)];
        yield 'another title' => [fn (string $page, string $message = '') => self::assertHtmlPageTitleSame($page, 'Another page', $message)];
        yield 'another value' => [fn (string $page, string $message = '') => self::assertHtmlInputValueSame($page, 'email', 'ben@example.test', $message)];
        yield 'an unchecked box' => [fn (string $page, string $message = '') => self::assertHtmlCheckboxChecked($page, 'input[name="email"]', $message)];
        yield 'another option' => [fn (string $page, string $message = '') => self::assertHtmlSelectedOption($page, '[data-day]', 'mo', $message)];
        yield 'an enabled button' => [fn (string $page, string $message = '') => self::assertHtmlSelectorDisabled($page, '[data-cancel]', $message)];
        yield 'a link to elsewhere' => [fn (string $page, string $message = '') => self::assertHtmlLink($page, 'Next', '/in', $message)];
        yield 'a node with text' => [fn (string $page, string $message = '') => self::assertHtmlSelectorEmpty($page, 'h1', $message)];
    }

    /** @return iterable<string, array{Closure(string): void}> */
    public static function notOneNode(): iterable
    {
        // Two nodes match: a check of one node does not read the first by chance.
        yield 'two nodes' => [fn (string $page) => self::assertHtmlSelectorTextSame($page, '[data-name]', 'Anna Example')];
        yield 'no node' => [fn (string $page) => self::assertHtmlSelectorTextSame($page, '[data-here]', 'Anna Example')];
        // Turned around, the check stays red: "no node has the text" is not what a missing node says.
        yield 'two nodes, turned around' => [fn (string $page) => self::assertHtmlNot($page, new HasSelectorText('[data-name]', 'Clara'))];
        yield 'no node, turned around' => [fn (string $page) => self::assertHtmlNot($page, new HasSelectorText('[data-here]', 'Clara'))];
    }

    /** @param Closure(string): void $check */
    #[DataProvider('passing')]
    public function test_it_passes_where_the_page_holds_what_is_asked(Closure $check): void
    {
        $check(Page::HTML);

        $this->addToAssertionCount(1);
    }

    /** @param Closure(string, string=): void $check */
    #[DataProvider('failing')]
    public function test_it_fails_where_the_page_does_not(Closure $check): void
    {
        $this->expectException(ExpectationFailedException::class);

        $check(Page::HTML);
    }

    /** @param Closure(string, string): void $check */
    #[DataProvider('failing')]
    public function test_a_failure_shows_the_message_of_the_test_before_its_own(Closure $check): void
    {
        try {
            $check(Page::HTML, 'The cart shows what the customer chose.');
        } catch (ExpectationFailedException $expectationFailedException) {
            self::assertStringStartsWith("The cart shows what the customer chose.\nFailed asserting that (page", $expectationFailedException->getMessage());

            return;
        }

        self::fail('The check passed.');
    }

    public function test_a_failure_without_a_message_keeps_its_text(): void
    {
        try {
            // A whole page: the parsers of Symfony 7.4 and 8.1 add a missing head differently.
            self::assertHtmlSelectorCount('<html><head></head><body><ul><li>a</li></ul></body></html>', 'li', 2);
        } catch (ExpectationFailedException $expectationFailedException) {
            self::assertSame(
                "Failed asserting that (page) has 2 nodes matching \"li\".\nThe selector \"li\" finds 1 nodes.\n\nIn (page):\n  <html>\n    <head>\n    <body>\n      <ul>\n        <li>\n          a",
                $expectationFailedException->getMessage(),
            );

            return;
        }

        self::fail('The check passed.');
    }

    public function test_a_check_of_one_node_shows_the_message_of_the_test_before_its_own(): void
    {
        try {
            self::assertHtmlSelectorTextSame(Page::HTML, '[data-name]', 'Anna Example', 'The total shows the sum.');
        } catch (NotOneNode $notOneNode) {
            self::assertStringStartsWith("The total shows the sum.\n(page): a check of one node needs exactly one match.", $notOneNode->getMessage());
            self::assertNotInstanceOf(NotOneNode::class, $notOneNode->getPrevious(), 'PHPUnit prints a previous exception a second time.');

            return;
        }

        self::fail('The check passed on two nodes.');
    }

    public function test_a_check_of_one_node_without_a_message_keeps_its_text(): void
    {
        try {
            self::assertHtmlSelectorTextSame(Page::HTML, '[data-name]', 'Anna Example');
        } catch (NotOneNode $notOneNode) {
            self::assertStringStartsWith('(page): a check of one node needs exactly one match.', $notOneNode->getMessage());

            return;
        }

        self::fail('The check passed on two nodes.');
    }

    public function test_a_value_that_is_not_a_page_shows_the_message_of_the_test_first(): void
    {
        try {
            self::assertHtmlSelectorExists(42, 'p', 'The page renders.');
        } catch (NotAPage $notaPage) {
            self::assertStringStartsWith("The page renders.\nA check of HTML reads ", $notaPage->getMessage());

            return;
        }

        self::fail('The check passed on a number.');
    }

    public function test_a_failure_names_the_path_what_was_found_and_the_region(): void
    {
        try {
            self::assertHtmlSelectorTextSame(Html::of(Page::HTML)->within('[data-people]'), '[data-name="Ben"]', 'Anna Example');
        } catch (ExpectationFailedException $expectationFailedException) {
            self::assertStringContainsString('(page) > [data-people] has one node matching "[data-name="Ben"]" with the text "Anna Example"', $expectationFailedException->getMessage());
            self::assertStringContainsString('Its text is "Ben".', $expectationFailedException->getMessage());
            // @phpstan-ignore html.markupAsString (the message shows the region as markup)
            self::assertStringContainsString('<li data-name="Ben" class="rounded-lg">', $expectationFailedException->getMessage());

            return;
        }

        self::fail('The check passed on another text.');
    }

    /** @param Closure(string): void $check */
    #[DataProvider('notOneNode')]
    public function test_a_check_of_one_node_fails_on_none_or_two_either_way(Closure $check): void
    {
        $this->expectException(NotOneNode::class);
        $this->expectExceptionMessage('a check of one node needs exactly one match.');

        $check(Page::HTML);
    }

    public function test_a_check_turned_around_says_so(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('(page) does not have a node matching "[data-people]"');

        self::assertHtmlNot(Page::HTML, new HasSelector('[data-people]'));
    }

    public function test_a_check_turned_around_holds_where_the_check_does_not(): void
    {
        self::assertHtmlNot(Page::HTML, new HasSelectorText('[data-name="Ben"]', 'Anna'));
        self::assertHtmlNot(Page::HTML, new HasSelectorTextContaining('h1', 'Montag'));
    }

    public function test_not_turns_only_a_check_that_says_has(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Not(new class extends Constraint implements RegionCheck
        {
            use ChecksARegion;

            public function toString(): string
            {
                return 'is a page';
            }

            public function holds(Html $html): bool
            {
                return true;
            }

            public function found(Html $html): string
            {
                return '';
            }
        });
    }

    public function test_a_class_check_needs_a_class(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HasSelectorClass('[data-name]', ' ');
    }

    public function test_the_last_selected_option_wins_and_the_first_stands_in_for_none(): void
    {
        self::assertHtmlSelectedOption('<select><option value="a" selected><option value="b" selected></select>', 'select', 'b');
        self::assertHtmlSelectedOption('<select><option value="a"><option value="b"></select>', 'select', 'a');
    }

    public function test_the_first_legend_of_a_disabled_fieldset_stays_enabled(): void
    {
        $page = '<fieldset disabled><legend><button data-in-legend>a</button></legend><legend><button data-in-second>b</button></legend></fieldset>';

        self::assertHtmlNot($page, new IsDisabled('[data-in-legend]'));
        self::assertHtmlSelectorDisabled($page, '[data-in-second]');
    }
}
