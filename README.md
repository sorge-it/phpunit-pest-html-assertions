# HTML assertions for PHPUnit and Pest

[![Tests](https://github.com/sorge-it/phpunit-pest-html-assertions/actions/workflows/tests.yml/badge.svg)](https://github.com/sorge-it/phpunit-pest-html-assertions/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/sorge-it/phpunit-pest-html-assertions)](https://packagist.org/packages/sorge-it/phpunit-pest-html-assertions)
[![Total Downloads](https://img.shields.io/packagist/dt/sorge-it/phpunit-pest-html-assertions)](https://packagist.org/packages/sorge-it/phpunit-pest-html-assertions)
[![License](https://img.shields.io/packagist/l/sorge-it/phpunit-pest-html-assertions)](LICENSE.md)

Check rendered HTML with CSS selectors, never as a string. Built for tests that AI coding agents
write and run: in Laravel, Livewire, Symfony and TYPO3.

```php
expect($this->get('/cart'))
    ->toHaveSelectorCount('[data-cart] li', 3)
    ->toHaveSelectorText('[data-total]', '42.00 EUR')
    ->not->toHaveSelector('[data-errors]');
```

**Works with:** PHPUnit · Pest · Laravel · Livewire · Symfony · TYPO3 · PSR-7 · Laravel Boost · PHPStan · Rector

## Why

Coding agents write a large share of our tests. Left alone, they check HTML the way it is easiest to
type: `assertSee('<span class="dot">')`, a regular expression over the markup. Those tests fail when
one more `<span>` wraps a dot, though the page looks the same. And they pass when the text they look
for sits only in an attribute or a script.

This package asks the DOM instead. `symfony/dom-crawler` parses the page with `Dom\HTMLDocument`, as
a browser does, and every check selects with CSS.

## Built for coding agents

Each part of the package acts at one step of the loop in which an agent writes a test:

| Step | Part |
|---|---|
| Before the agent writes | A [Laravel Boost](#laravel-boost) skill gives the agent the checks and the rules for selectors. |
| When the agent checks its work | The [PHPStan rules](#phpstan) `html.markupAsString` and `html.classAsString` report a check of markup or of a class as a string and say what to use instead. |
| When a test fails | The [message](#when-a-check-fails) names the region, what was asked, what was found, and shows the HTML of the region. The agent can fix the test without a browser. |
| In an existing suite | A [Rector rule](#rector) rewrites the mechanical forms of crawler code. |

## Parts

The package has four parts:

| Part | Namespace | What it holds |
|---|---|---|
| PHPUnit | `SorgeIt\PhpunitPestHtmlAssertions\PHPUnit` | the constraints, `Html` (a page or a region of it) and the `AssertsHtml` trait |
| Pest | `SorgeIt\PhpunitPestHtmlAssertions\Pest` | the expectations and the function `html()` |
| PHPStan | `SorgeIt\PhpunitPestHtmlAssertions\PHPStan` | the rules `html.markupAsString` and `html.classAsString`, which report a check of markup or of a class as a string |
| Rector | `SorgeIt\PhpunitPestHtmlAssertions\Rector` | a rule that rewrites the mechanical forms of crawler code |

## Requirements

- PHP 8.3, 8.4 or 8.5
- PHPUnit 12.5 or 13
- Pest 4 or 5, optional, for the expectations
- the PHP extensions `dom` and `mbstring`
- Symfony DomCrawler and CssSelector 7.4 or 8.1

The CI tests three stacks: PHP 8.3 with PHPUnit 12, Pest 4, Laravel 12 and Symfony 7.4; PHP 8.4 with
the newest versions; PHP 8.5 with the versions of `composer.lock`.

## Installation

```sh
composer require --dev sorge-it/phpunit-pest-html-assertions
```

Where Pest is installed, Composer registers the expectations. `tests/Pest.php` needs no line.

## Quick start

**Pest:**

```php
it('lists the items of the cart', function () {
    expect($this->get('/cart'))
        ->toHaveSelectorCount('[data-cart] li', 3)
        ->toHaveAnySelectorText('[data-cart] li', 'Apple');
});
```

**PHPUnit:**

```php
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\AssertsHtml;

final class CartTest extends TestCase
{
    use AssertsHtml;

    public function test_it_lists_the_items_of_the_cart(): void
    {
        $response = $this->get('/cart');

        self::assertHtmlSelectorCount($response, '[data-cart] li', 3);
        self::assertHtmlAnySelectorTextSame($response, '[data-cart] li', 'Apple');
    }
}
```

Every method of the trait starts with `assertHtml`. So the trait also works in a Symfony
`WebTestCase`, which has its own `assertSelectorExists()` and similar methods.

## What a check reads

Each check takes one of these:

- a string of HTML;
- a Symfony `Crawler`;
- a Laravel `TestResponse` (also a streamed one), `TestView` or `TestComponent`;
- a Livewire `Testable`;
- a PSR-7 `ResponseInterface`, for example in a TYPO3 functional test;
- a Symfony `Response`;
- an `Html` of this package.

The package tells the kinds apart by class. None of their frameworks is a dependency. Another value
stops the check with `NotAPage`, also under `->not`: a negated check cannot pass on a wrong value.

A string is parsed as a whole page, the way a browser parses it. A fragment of a table without its
table loses its tags: `<td>x</td>` alone becomes the text `x`. Wrap such a fragment in `<table>`.

## Checks

Text is compared with its white space collapsed and trimmed. The text of a `script`, `style`,
`template`, `noscript` or `head` inside a node does not count. A node asked for by name keeps its
own text. The `Any…` checks are for "some node".

Each row gives the call and the sentence of its message when it fails, after the path of the region.

| Expectation (Pest) | Method (PHPUnit trait) | Fails with: "Failed asserting that (page) …" |
|---|---|---|
| `toHaveSelector('[data-cart]')` | `assertHtmlSelectorExists` | has a node matching "[data-cart]" |
| — | `assertHtmlSelectorNotExists` | does not have a node matching "[data-cart]" |
| `toHaveSelectorCount('li', 3)` | `assertHtmlSelectorCount` | has 3 nodes matching "li" |
| `toHaveSelectorCountAtLeast('li', 1)` | `assertHtmlSelectorCountAtLeast` | has at least 1 nodes matching "li" |
| `toHaveSelectorText('h1', 'Orders')` | `assertHtmlSelectorTextSame` | has one node matching "h1" with the text "Orders" |
| `toHaveSelectorTextContaining('h1', 'Ord')` | `assertHtmlSelectorTextContains` | has one node matching "h1" whose text contains "Ord" |
| `toHaveAnySelectorText('li', 'Apple')` | `assertHtmlAnySelectorTextSame` | has a node matching "li" with the text "Apple" |
| `toHaveAnySelectorTextContaining('li', 'App')` | `assertHtmlAnySelectorTextContains` | has a node matching "li" whose text contains "App" |
| `toHaveSelectorAttribute('a', 'href', '/next')` | `assertHtmlSelectorAttribute` | has one node matching "a" whose attribute "href" is "/next" |
| `toHaveSelectorAttribute('button', 'disabled')` | `assertHtmlSelectorAttribute` | has one node matching "button" with the attribute "disabled" |
| `toHaveSelectorAttributeContaining('a', 'href', 'page=2')` | `assertHtmlSelectorAttributeContains` | has one node matching "a" whose attribute "href" contains "page=2" |
| `toHaveSelectorAttributeNamed('main', 'wire:poll')` | `assertHtmlSelectorAttributeNamed` | has one node matching "main" with an attribute whose name starts with "wire:poll" |
| `toHaveSelectorClass('[data-status]', 'bg-red-500')` | `assertHtmlSelectorClass` | has one node matching "[data-status]" with the class "bg-red-500" |
| `toHaveText('Apple Pear')` | `assertHtmlTextSame` | has the text "Apple Pear" |
| `toHaveTextContaining('Apple')` | `assertHtmlTextContains` | has a text that contains "Apple" |
| `toHaveTextCount('Apple', 1)` | `assertHtmlTextCount` | has the text "Apple" 1 times |
| `toAppearBefore('[data-head]', '[data-body]')` | `assertHtmlSelectorBefore` | has the node matching "[data-head]" before the node matching "[data-body]" |
| `toHaveTitle('Orders')` | `assertHtmlPageTitleSame` | has the title "Orders" |
| `toHaveInputValue('email', 'anna@example.com')` | `assertHtmlInputValueSame` | has one field named "email" with the value "anna@example.com" |
| `toBeChecked('[data-agree]')` | `assertHtmlCheckboxChecked` | has one checked box matching "[data-agree]" |
| `toHaveSelectedOption('[data-country]', 'de')` | `assertHtmlSelectedOption` | has one select matching "[data-country]" with the option "de" chosen |
| `toBeDisabled('[data-send]')` | `assertHtmlSelectorDisabled` | has one disabled node matching "[data-send]" |
| `toHaveLink('Next', '/page/2')` | `assertHtmlLink` | has a link "Next" to "/page/2" |
| `toBeEmptyNode('[data-errors]')` | `assertHtmlSelectorEmpty` | has one empty node matching "[data-errors]" |

What the checks read in detail:

- A field's value (`toHaveInputValue`) is an input's `value`, a textarea's text, or a select's chosen option.
- The chosen option is the last one marked `selected`, else the first, as a browser does.
- A node is disabled by itself, or by a disabled `fieldset`, unless it stands in that fieldset's first `legend`.
- A class check needs every class given, among others, in any order. An empty class list is refused.
- `toBeEmptyNode` is not `toBeEmpty`: Pest has one of its own.

### One node or none

A check of one node needs exactly one match. Zero or two matches throw `NotOneNode`, so a test never
reads the first of several by chance. `->not` does not turn that around: `not->toHaveSelectorText()`
on a node that is not there stops with an error. It does not pass. To say that no node is there,
use `not->toHaveSelector()`.

`->not` in Pest turns a check around. Pest then writes its own text, for example
`Expecting … not to have selector '[data-cart]' 'The cart is empty after checkout.'.` The text lists
each argument, also the message of the test, but not the region. `assertHtmlNot()` of the PHPUnit
trait keeps the text of this package, with the region. To give the reason and the region in Pest,
count zero nodes: `toHaveSelectorCount('[data-cart]', 0, 'The cart is empty after checkout.')`.

`within()`, `frame()` and `eachMatch()` find the region that the checks after them, or in the
callback of `eachMatch()`, read. A missing region stops the test, also under `->not`: `within()` and
`frame()` with `NotOneNode`, a frame without `srcdoc` with `NotAPage`, `eachMatch()` with `NoMatch`.
`->not->eachMatch()` stops with a `LogicException`: it would pass where one match fails. Turn the
checks in the callback around. After `within()` or `frame()`, Pest drops a `->not` before the next
`within()` or `frame()`. Turn the check after them around.

### The reason for a check

Each check takes a last, optional parameter `string $message = ''`, as the checks of PHPUnit and
Pest do. Give the reason for the check. Only the test knows it. A failure shows it first, before the
text of this package:

```php
expect($response)->toHaveSelectorCount('[data-cart] li', 3, 'The cart keeps the items of the last visit.');
self::assertHtmlSelectorCount($response, '[data-cart] li', 3, 'The cart keeps the items of the last visit.');
```

```
The cart keeps the items of the last visit.
Failed asserting that (page) has 3 nodes matching "[data-cart] li".
The selector "[data-cart] li" finds 2 nodes.
…
```

- `toHaveSelectorAttribute()` and `assertHtmlSelectorAttribute()` have an optional `$value` before
  the message. Give the message by name there:
  `toHaveSelectorAttribute('button', 'disabled', message: 'The form waits for the consent.')`.
- `NotOneNode`, `NotAPage` and `NoMatch` show the message first too.
- `eachMatch($selector, $callback, $message)` shows it where no node matches. Each check in the
  callback takes its own message.
- `within()` and `frame()` take no message. To give a reason, check the count first:
  `toHaveSelectorCount($selector, 1, $message)`.
- Under `->not`, see [One node or none](#one-node-or-none).

## Regions and values

`html($value)` turns a page into an `Html`. Pest passes a method it does not know to the object it
expects and keeps checking the result, so on an `Html` the methods below chain like expectations.

```php
use function SorgeIt\PhpunitPestHtmlAssertions\Pest\html;

// A region: the page holds it exactly once, or the test stops with NotOneNode. Every check after it looks inside it.
expect(html($page))->within('[data-cart]')
    ->toHaveSelectorCount('li', 3)
    ->not->toHaveSelector('[data-errors]');

// The srcdoc of an iframe, as a page of its own.
expect(html($page))->frame('iframe[data-preview]')->toHaveSelectorText('p', 'Hello');

// Values, in the order of the page.
expect(html($page))->texts('[data-cart] [data-name]')->toBe(['Apple', 'Pear']);
expect(html($page))->rawTexts('[data-note]')->toBe(["Line one\nLine two"]);
expect(html($page))->attributes('[data-item]', 'data-id')->toBe(['1', '2']);

// The same checks on every match. No match stops the test with NoMatch: a loop over nothing would check nothing.
// `:scope` is the match itself.
expect($page)->eachMatch('[data-avatar]', fn ($avatar) => $avatar->toHaveSelectorClass(':scope', 'rounded-full'));

// Every attribute value of the region, whatever the name of the attribute.
expect(html($page)->attributeValues())->each->not->toContain('javascript:');
```

A negated check on a region that is not there would always pass. `within()` prevents that: a
missing region stops the test, also under `->not`.

**A region finds what lies below its element, as `querySelectorAll` of a browser does.** The element
itself is not a match: in `within('[data-cart]')`, the selector `[data-cart]` finds nothing, and
`body li` still finds the items, because a selector is read against the whole page. `:scope` alone
names the element of the region. Symfony translates `:scope` by position, so `:scope.x` or
`:scope > li` would find the wrong nodes. `Html` refuses both.

In plain PHP, `Html` has the same methods: `Html::of($value)->within($selector)`, `->frame()`,
`->matches()`, `->texts()`, `->rawTexts()`, `->attributes()`, `->attributeValues()`, `->count()`,
`->text()`.

## When a check fails

The message gives the path of regions, what was asked, what was found, and the region itself as
indented HTML, cut at 40 lines:

```
Failed asserting that (page) > [data-cart] has one node matching "[data-name="Pear"]" with the text "Pear, ripe".
Its text is "Pear".

In (page) > [data-cart]:
  <ul data-cart>
    <li data-name="Apple" class="rounded-lg bg-green-500">
      Apple
    <li data-name="Pear" class="rounded-lg">
      Pear
```

A check of one node that finds none or two says so, with the same region:

```
NotOneNode: (page) > [data-cart]: a check of one node needs exactly one match. The selector "[data-name]" finds 2 nodes.
```

`dump()` and `dd()` of Pest show the same path and region.

## Which selector

1. The element, its role, `aria-*`, its label or its visible text.
2. A `data-*` marker: the functional marker that the app (Alpine, scripts) and the tests share. A CSS
   class is for the look, not for a selector.
3. A class is a value to check (`toHaveSelectorClass('[data-status]', 'bg-red-500')`), not the
   selector. Markup of a vendor such as Filament is the exception: its own classes (`fi-ta-cell`,
   `fi-badge`) are its functional hooks.

Escape a colon or a dot in an attribute name: `[wire\:model="name"]`, `[wire\:poll\.10s]`.

## PHPStan

With `phpstan/extension-installer`, `extension.neon` loads automatically. Without it, include
`vendor/sorge-it/phpunit-pest-html-assertions/extension.neon`. Two rules read the files in a
directory named `tests` or `Tests`:

| Rule | Reports | Use instead |
|---|---|---|
| `html.markupAsString` | a check of markup as a string: `assertSeeHtml('<b>')`, `toContain('<div')` | a check with a CSS selector |
| `html.classAsString` | a check that a string holds a class: `toContain('lg:grid-cols-4')` | `toHaveSelectorClass()` or `assertHtmlSelectorClass()` |

Both rules read literals. PHPStan cannot know whether a string is HTML, so a rule reports a literal
whose form is rare outside of HTML. The calls they read:

- the checks of Pest: `toContain`, `toBe` (only `html.markupAsString`), `toStartWith`, `toEndWith`, `toMatch`;
- the PHPUnit assertions of a string: `assertStringContainsString` and `assertStringNotContainsString`
  with their `IgnoringCase` forms, `assertStringContainsStringIgnoringLineEndings`,
  `assertStringStartsWith`, `assertStringStartsNotWith`, `assertStringEndsWith`,
  `assertStringEndsNotWith`, `assertMatchesRegularExpression`, `assertDoesNotMatchRegularExpression`;
- the searches and cuts of Laravel's `Str` and `Stringable`: `contains`, `containsAll`,
  `doesntContain`, `startsWith`, `endsWith`, `substrCount`, `match`, `matchAll`, `isMatch`, `test`,
  `after`, `afterLast`, `before`, `beforeLast`, `between`, `betweenFirst`;
- the functions of PHP: `preg_match`, `preg_match_all`, `str_contains`, `str_starts_with`,
  `str_ends_with`, `strpos`, `substr_count` and their `i` and `mb_` forms.

### Markup as a string

`html.markupAsString` reports:

- `assertSeeHtml`, `assertDontSeeHtml`, `assertSeeHtmlInOrder` and `assertSeeInOrder`, always;
- `assertSee` and `assertDontSee` with escaping off;
- each call of the list above whose literal holds markup. A cut of `Str` also when its literal is a
  bracket of a tag alone: `Str::betweenFirst($html, 'data-x', '>')`.

Markup is a tag (also in a regular expression, `<div[^>]*>`), an attribute of HTML with its value, or
the name of a `data-*` or `wire:` attribute. After `[`, it is a CSS selector and not reported.

### A class as a string

`html.classAsString` reports a check that a string holds a class. Such a check passes when the class
is anywhere: on another element, inside a longer class, in a script or in a comment.

```php
expect($html)->toContain('lg:grid-cols-4');                                // reported
expect(str_contains($html, 'lg:grid-cols-4'))->toBeTrue();                 // reported
expect($html)->toHaveSelectorClass('[data-highlights]', 'lg:grid-cols-4'); // the element has the class
```

It reports a check when all of these are true:

- **The check searches a string, or checks the result of a search.** `toBe` compares the whole
  string and is not read. A search is read only as the value of a check:
  `expect(substr_count($html, 'lg:flex'))->toBe(2)`, `$this->assertTrue(Str::contains($html, 'lg:flex'))`.
  A search in an `if` or in a closure is not a check. A cut (`Str::after`) is not read: its result
  does not tell whether it found its text.
- **The check passes when the class is found.** A negative check is not reported:
  `->not->toContain()`, `assertStringNotContainsString()`, `expect(str_contains(…))->toBeFalse()`,
  `assertSame(0, substr_count(…))`, `Str::doesntContain()`. It fails when the class is anywhere, so it
  is stricter than a check of the DOM. A check that does not say found or absent is not reported
  either: `toBeBool()`, `toBe($value)`, `toBeTruthy()` on a position, which is 0 at the start of the
  string, and `toEqual(0)` or `assertEquals(0, …)`, which also pass for `false`.
- **The searched value is a string,** also `string|false` from `getContent()` or `?string`. An array,
  a collection or `mixed` is not reported. A call that reads the class attribute directly,
  `$element->getAttribute('class')`, is not reported. A variable, a cast or `?? ''` around it is
  reported.
- **Each word of the literal can be in a `class` attribute, and one word is a utility of Tailwind:**
  with a value in `[ ]` (`max-w-[90rem]`), or after a variant (`lg:grid-cols-4`, `hover:underline`,
  `group-hover/item:underline`, `data-[state=open]:block`, `@md:flex`, `lg:flex!`). After a variant,
  the utility has a digit, a `-`, a `/` or `[ ]`, or it is a known word such as `flex`. So
  `after:today`, `first:name` and `mailto:` are not classes. A value of letters alone in `[ ]` is a
  key: `errors-[name]`.
- **The literal has no markup.** A literal with markup is for `html.markupAsString`.

The rule joins a literal with a variable before it reads it: `"lg:grid-cols-{$n}"` is a class,
`"after:{$date}"` is not. It reads a regular expression without its delimiters, `toMatch('/lg:flex/')`.
A pattern with other syntax is not read: a character class, a group, an alternative, a quantifier,
an anchor or an escape such as `\b`.

A plain word (`flex`), kebab-case (`header-grid`) and a custom property (`--row-bg: #fff`) are not
reported. In tests, the first two are often a header value, a slug or text. For a custom property in
a `style` attribute, use `toHaveSelectorAttributeContaining()`: the rule cannot tell that attribute
from CSS text.

A project that writes its classes in camelCase (`headerNav`) can turn on that form. In other
projects, camelCase in a test is usually a name of JavaScript or PHP.

```neon
parameters:
    htmlAssertions:
        camelCaseClasses: true
```

### Ignore a line on purpose

A check that compares a string on purpose gets an ignore comment on its line. Write the reason in
parentheses. PHPStan reports the comment when the rule no longer reports the line:

```php
expect($rewritten)->toBe($expected); // @phpstan-ignore html.markupAsString (the rewriter keeps every other byte)
expect($script)->toContain('lg:hidden'); // @phpstan-ignore html.classAsString (the script adds the class)
```

### What the rules do not see

- A string in a variable: `$needle = 'lg:flex'; expect($html)->toContain($needle);`.
- A class without a variant or `[ ]`: `expect(mb_substr_count($html, 'bg-red-500'))->toBe(1)`. Neither
  rule reports it.
- A search whose result goes into a variable first.

A project that does not check its tests with PHPStan can check them at level 0 for these rules.

## Rector

`SorgeIt\PhpunitPestHtmlAssertions\Rector\CrawlerToHtmlRector` rewrites three forms of crawler code:

| Before | After |
|---|---|
| `new Crawler($x)->filter($s)->count()` | `Html::of($x)->count($s)` |
| `new Crawler($x)->filter($s)->each(fn (Crawler $n): string => $n->text())` | `Html::of($x)->texts($s)` |
| `new Crawler($x)->filter($s)->each(fn (Crawler $n): ?string => $n->attr('a'))` | `Html::of($x)->attributes($s, 'a')` |

It rewrites only `new Symfony\Component\DomCrawler\Crawler($x)` where `$x` is a string. The texts
change slightly: `texts()` leaves out a script, a style or a `template` inside a node, and
`Crawler::text()` keeps them. Run the suite after the rewrite.

Add it to the `rector.php` of a project for a migration: `->withRules([CrawlerToHtmlRector::class])`.

## Laravel Boost

The package ships a skill for [Laravel Boost](https://github.com/laravel/boost) in
`resources/boost/skills/`. Boost finds it in every direct dependency and gives it to your coding
agent, so the agent writes these checks instead of string checks.

## Not in scope

- CSS: whether a rule takes effect is a question for a browser test (`getComputedStyle`).
- Accessibility: `axe-core` in a browser test.
- HTML snapshots: a snapshot takes every change as the new truth.

## Development

```sh
composer test   # Pint and Rector as a dry run, PHPStan at max, type coverage 100 %, the test suites
composer lint   # Rector, then Pint
```

Bug reports and pull requests are welcome on
[GitHub](https://github.com/sorge-it/phpunit-pest-html-assertions/issues).

## Security

Please report a security problem by email to github@sorge-it.de, not in a public issue.

## License

MIT. See [LICENSE.md](LICENSE.md).

## About the author

**Stefan Sorge** · PHP expert, builder, agentic engineer.
25 years of PHP and tech for 40+ startups. I build tools that humans and AI agents
read and act on the same way.

[GitHub](https://github.com/sorge-it) · [LinkedIn](https://www.linkedin.com/in/stefansorge/)
