# Changelog

## 1.4.0

### Added

- A guideline for Laravel Boost in `resources/boost/guidelines/core.blade.php`. Boost gives it to the
  agent with every task, the skill only when a task needs it. Boost installs both once the package is
  picked on `php artisan boost:install` or listed in `packages` of `boost.json`; the README said
  before that Boost found the skill by itself.
- The skill installs in Claude Code as a plugin of this repository, and in other agents with
  `npx skills add sorge-it/phpunit-pest-html-assertions`. See the README, section For coding agents.
- A demo in the README, recorded with VHS from the app in `demo/`.

### Fixed

- A failure points at the line of the test. Before, Pest showed the line in
  `src/PHPUnit/Assertion.php` of this package, and the line of the test came only further down. The
  package now adds its `src` directory to PHPUnit's `ExcludeList`, as PHPUnit does with its own
  code. The printed trace of PHPUnit leaves out these frames too. `HTML_ASSERTIONS_SHOW_FRAMES=1`,
  set in the shell, keeps them, for a bug report. The checkout of the package keeps them always.

### Changed

- The package requires Composer 2.1 (`composer-runtime-api: ^2.1`).
- Composer loads the new `src/PHPUnit/Autoload.php` in every process that loads `vendor/autoload.php`:
  it adds the `src` directory to PHPUnit's `ExcludeList` and has no other effect.
- A deprecation triggered inside the package names PHPUnit as its source, not a third party. Its
  category (self, direct, indirect) stays the same.
- A problem comes as a pull request, not as an issue: issues are closed. See CONTRIBUTING.md.
- A vulnerability is reported privately through GitHub, not by email. See SECURITY.md.

### Upgrade

- Composer 1 and Composer 2.0 stay on 1.3. Update Composer to 2.1 or later.
- A test that expects frames of this package in a trace runs with `HTML_ASSERTIONS_SHOW_FRAMES=1`
  set in the shell. In `phpunit.xml` it comes too late: Composer loads the package first.

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
