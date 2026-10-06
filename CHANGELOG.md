# Changelog

## 1.3.0

### Added

- Each check takes a last, optional parameter `string $message = ''`: the 23 check methods of the
  trait `AssertsHtml` (`assertHtml()` and `assertHtmlNot()` had it before), the 22 expectations of
  Pest and `eachMatch`. A failure shows the message first, as PHPUnit does. `NotOneNode` and
  `NotAPage` show it first too. Without a message, each failure keeps its text byte for byte.
- Under `->not`, Pest lists the message among its arguments, without the region. See the README,
  section One node or none.

### Fixed

- A value that is not a page stops a check with `NotAPage`, a `LogicException` like `NotOneNode`.
  `Html::of()` and `html()` throw it too. Before, they failed with an `AssertionFailedError`, and
  Pest's `->not` turned that into a pass. PHPUnit now counts it as an error, not as a failure.
- A missing region stops the test, also under `->not`. `within()` and `frame()` throw `NotOneNode`
  where the selector finds no node or more than one. `frame()` throws `NotAPage` for a frame without
  `srcdoc`. `eachMatch()` throws the new `NoMatch` where no node matches. Before, each failed with an
  `AssertionFailedError`, and `->not` turned that into a pass.
- `->not->eachMatch()` stops with a `LogicException`. Before, it passed where one match failed.

### Upgrade

- A call needs no change for the message. The parameter is optional and comes last.
- A test with `->not->eachMatch()` turns the checks in the callback around instead.
- A subclass that overrides a method of `AssertsHtml` adds `string $message = ''` as its last
  parameter. Without it, PHP stops with "must be compatible".
- Code that expects or catches an `AssertionFailedError` for a value that is not a page expects
  `SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\NotAPage`.
- A test that expects a failure for a missing region expects
  `SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\NotOneNode`,
  `SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\NotAPage` or
  `SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\NoMatch`. PHPUnit counts these as errors.
- `frame()` counts as one assertion, not two.

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
