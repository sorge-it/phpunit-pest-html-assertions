# Changelog

## 1.2.0

### Added

- The PHPStan rule `html.classAsString`. It reports a check that a string holds a utility of
  Tailwind with `[ ]` or after a variant, for example `toContain('lg:grid-cols-4')` or
  `expect(str_contains($html, 'lg:flex'))->toBeTrue()`. The message names `toHaveSelectorClass()`
  and `assertHtmlSelectorClass()`. See the README, section PHPStan.
- The parameter `htmlAssertions.camelCaseClasses`. When it is `true`, the rule also reports a class
  in camelCase. The default is `false`.
- `html.markupAsString` also reads `assertStringContainsStringIgnoringCase`,
  `assertStringNotContainsStringIgnoringCase`, `assertStringContainsStringIgnoringLineEndings`,
  `assertStringStartsNotWith`, `assertStringEndsNotWith`, `Str::doesntContain` and `Str::isMatch`.

### Fixed

- `html.markupAsString` reported a call with `?->` two times. It now reports it one time.
- `composer.json` requires `ext-mbstring`. The package used `mb_trim()` already, which needs the
  extension also with `symfony/polyfill-php84`.

### Upgrade

- `extension.neon` loads the new rule. No change to the configuration is necessary. A project with
  `^1.1` gets it at its next `composer update`, and PHPStan can then report new errors.
  `html.markupAsString` can also report new errors in the calls that it now reads.
- To turn the new rule off, ignore its identifier:

  ```neon
  parameters:
      ignoreErrors:
          - identifier: html.classAsString
  ```
