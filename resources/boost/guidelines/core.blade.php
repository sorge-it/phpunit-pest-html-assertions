## sorge-it/phpunit-pest-html-assertions

- Check rendered HTML (a response, a view, a component, a Livewire test, a string of markup) with CSS selectors, never as a string: no `assertSee`, `assertSeeHtml`, `toContain('<…')`, `str_contains()` or regular expression over markup.
- Pest: `expect($response)->toHaveSelectorText('[data-total]', '42.00 EUR')`. PHPUnit: the trait `SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\AssertsHtml`, `self::assertHtmlSelectorTextSame($response, '[data-total]', '42.00 EUR')`.
- Select by tag, role, `aria-*`, label or text, then by a `data-*` marker. A class is a value to check with `toHaveSelectorClass()`, not a selector.
- A check of one node needs exactly one match. Narrow to a region with `expect(html($page))->within('[data-cart]')`; `html()` is the function `SorgeIt\PhpunitPestHtmlAssertions\Pest\html`.
- Give a check its reason as the last argument: the failure message shows it first. After the optional value of `toHaveSelectorAttribute()`, pass it by name: `message: '…'`.
- The PHPStan rules `html.markupAsString` and `html.classAsString` report a check of markup or of a class as a string. Rewrite the check; ignore a line only with a reason.
- Read the skill `phpunit-pest-html-assertions` before you write or change such a test.
