---
name: phpunit-pest-html-assertions
description: "Checks rendered HTML in PHPUnit and Pest tests with CSS selectors, never as a string. Activate when a test checks the HTML of a response, a Blade view or component, a Livewire component, a TYPO3 or Symfony response, or a string of markup; when a test uses assertSee, assertSeeHtml, toContain or a regular expression on HTML; when a test reads text or attributes from a page; or when PHPStan reports html.markupAsString or html.classAsString."
license: MIT
metadata:
  author: sorge-it
---

# HTML assertions for PHPUnit and Pest

## Rules

- Check HTML through the DOM with a CSS selector. Never compare markup as a string: no `assertSee`, `assertSeeHtml`, `toContain('<div')` or regular expression on HTML.
- Never search the HTML for a class as text: `toContain('lg:grid-cols-4')` passes when the class is on any element, in a script or in a comment. Ask the element with `toHaveSelectorClass()`.
- Select by element, role, `aria-*`, label or visible text first, then by a `data-*` marker. A CSS class is a value to check, not a selector. Vendor markup (for example Filament's `fi-*` classes) is the exception.
- Every check takes a string of HTML, a Symfony `Crawler`, a Laravel `TestResponse`, `TestView` or `TestComponent`, a Livewire `Testable`, a PSR-7 `ResponseInterface`, a Symfony `Response`, or an `Html`.
- A string is parsed as a whole page. A table cell without its table loses its tags: wrap a fragment such as `<td>x</td>` in `<table>`.
- Escape a colon or a dot in an attribute name: `[wire\:model="name"]`.

## Pest expectations

The expectations are registered by Composer; `tests/Pest.php` needs no line.

```php
expect($response)->toHaveSelector('[data-cart]');
expect($response)->toHaveSelectorCount('[data-cart] li', 3);
expect($response)->toHaveSelectorCountAtLeast('li', 1);
expect($response)->toHaveSelectorText('h1', 'Orders');
expect($response)->toHaveSelectorTextContaining('h1', 'Ord');
expect($response)->toHaveAnySelectorText('li', 'Anna');
expect($response)->toHaveAnySelectorTextContaining('li', 'Ann');
expect($response)->toHaveSelectorAttribute('a[data-next]', 'href', '/page/2');
expect($response)->toHaveSelectorAttribute('button[data-send]', 'disabled');
expect($response)->toHaveSelectorAttributeContaining('a[data-next]', 'href', 'page=2');
expect($response)->toHaveSelectorAttributeNamed('main', 'wire:poll');
expect($response)->toHaveSelectorClass('[data-status]', 'bg-red-500');
expect($response)->toHaveText('Anna Ben');
expect($response)->toHaveTextContaining('Anna');
expect($response)->toHaveTextCount('Anna', 1);
expect($response)->toAppearBefore('[data-head]', '[data-body]');
expect($response)->toHaveTitle('Orders');
expect($response)->toHaveInputValue('email', 'anna@example.com');
expect($response)->toBeChecked('[data-agree]');
expect($response)->toHaveSelectedOption('[data-country]', 'de');
expect($response)->toBeDisabled('[data-send]');
expect($response)->toHaveLink('Next', '/page/2');
expect($response)->toBeEmptyNode('[data-errors]');
```

Use `toBeEmptyNode`, not `toBeEmpty`: Pest has its own `toBeEmpty`.

The PHPUnit trait `SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\AssertsHtml` has the same checks as methods, for example `assertHtmlSelectorExists`, `assertHtmlSelectorTextSame` and `assertHtmlSelectorAttribute`.

## The reason for a check

Give the reason for a check as its last parameter `$message`, not as a comment. A failure shows it first. `within()` and `frame()` take no message. After the optional `$value` of `toHaveSelectorAttribute()`, give the message by name:

```php
expect($response)->toHaveSelectorCount('[data-cart] li', 3, 'The cart keeps the items of the last visit.');
expect($response)->toHaveSelectorAttribute('button[data-send]', 'disabled', message: 'The form waits for the consent.');
```

## One node or none

A check of one node (`toHaveSelectorText`, `toHaveSelectorAttribute`, `toHaveSelectorClass`, `toBeChecked`, `toBeDisabled`, `toBeEmptyNode` and similar) needs exactly one match. Zero or two matches throw `NotOneNode`, also under `->not`. To say that a node is not there, use `not->toHaveSelector()`:

```php
expect($response)->not->toHaveSelector('[data-errors]');
```

Under `->not`, Pest lists the message among the arguments, without the region. To give the reason and the region, count zero nodes:

```php
expect($response)->toHaveSelectorCount('[data-errors]', 0, 'A valid form shows no error.');
```

## Regions and values

`html($value)` turns a page into an `Html`. On an `Html`, the methods below chain like expectations.

```php
use function SorgeIt\PhpunitPestHtmlAssertions\Pest\html;

// A region: the page holds it exactly once, or the test stops with NotOneNode. Every check after it looks inside it.
expect(html($response))->within('[data-cart]')
    ->toHaveSelectorCount('li', 3)
    ->not->toHaveSelector('[data-errors]');

// The srcdoc of an iframe, as a page of its own.
expect(html($response))->frame('iframe[data-preview]')->toHaveSelectorText('p', 'Hello');

// Values, in the order of the page.
expect(html($response))->texts('[data-cart] [data-name]')->toBe(['Apple', 'Pear']);
expect(html($response))->rawTexts('[data-note]')->toBe(["Line one\nLine two"]);
expect(html($response))->attributes('[data-item]', 'data-id')->toBe(['1', '2']);

// Every attribute value of the region.
expect(html($response)->attributeValues())->each->not->toContain('javascript:');
```

`eachMatch` runs the same checks on every match. No match stops the test with `NoMatch`. Never write `->not->eachMatch()`, `->not->within()` or `->not->frame()`: turn the checks inside or after them around.

```php
expect($response)->eachMatch('[data-avatar]', fn ($avatar) => $avatar->toHaveSelectorClass(':scope', 'rounded-full'));
```

A region reads like `querySelectorAll`: it finds nodes below its element, not the element itself. `:scope` alone names the element of the region. `:scope > li` and `:scope.x` are refused.

In plain PHP: `Html::of($value)->within($selector)`, `->frame()`, `->matches()`, `->texts()`, `->rawTexts()`, `->attributes()`, `->attributeValues()`, `->count()`, `->text()`.

## Text

Text is compared with white space collapsed and trimmed. The text of a `script`, `style`, `template`, `noscript` or `head` inside the node does not count.

## PHPStan

Two rules report a string check in a test. Replace the check. Do not change the code only to stop the report:

| Rule | Reports | Use instead |
|---|---|---|
| `html.markupAsString` | markup in a string check: `assertSeeHtml('<b>')`, `toContain('<div')` | a check with a CSS selector |
| `html.classAsString` | a Tailwind class in a string check: `toContain('lg:flex')`, `expect(str_contains($html, 'lg:flex'))->toBeTrue()` | `toHaveSelectorClass()` or `assertHtmlSelectorClass()` |

```php
expect($response)->toHaveSelectorClass('[data-highlights]', 'lg:grid-cols-4');
```

Ignore a line only when the string is not HTML, or when the test compares a string on purpose. Give the reason:

```php
expect($script)->toContain('lg:hidden'); // @phpstan-ignore html.classAsString (the script adds the class)
```

A project whose classes are in camelCase (`headerNav`) sets `parameters.htmlAssertions.camelCaseClasses: true` in `phpstan.neon`. Then `html.classAsString` also reports those classes.
