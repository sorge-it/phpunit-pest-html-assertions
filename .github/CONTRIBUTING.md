# Contributing

Pull requests are welcome. The issue tracker is closed: report a bug as a pull request with a
failing test; a fix is welcome but not required. Report a vulnerability privately instead; see the
[security policy](https://github.com/sorge-it/phpunit-pest-html-assertions/security/policy).
Everyone who takes part follows the
[Code of Conduct](https://github.com/sorge-it/phpunit-pest-html-assertions/blob/main/.github/CODE_OF_CONDUCT.md).

## Scope

Read [When to use it and when not to](https://github.com/sorge-it/phpunit-pest-html-assertions#when-to-use-it-and-when-not-to)
and [Scope](https://github.com/sorge-it/phpunit-pest-html-assertions#scope) in the README first. A
feature outside the scope, such as a check of the DOM after a script ran, will not be merged.

- There is no `tests/Scope/out.php` with a guard for each `out` row: this package copies no
  reference implementation, so no out-of-scope behavior can come back from one. The row on
  `<template>` has its test in `tests/PHPUnit/HtmlTest.php` instead.
- The list "Do not use it for" in the README gives the reason and the tool for the other `out` rows.
- A new `out` row comes with its reason, and with a test that shows the package does not do it where
  it easily could, as the test of `<template>` does.

## Setup

You need Composer 2.4 or later, PHP 8.3 or a later 8.x with the `dom`, `intl` and `mbstring`
extensions, and pcov or Xdebug for line coverage. Without `intl`, the lowest versions fail in the
Rector tests.

```sh
composer install
```

`composer.json` sets the platform to PHP 8.3, so `composer.lock` holds versions that run on the
lowest PHP the package supports.

## Tests

Before you open a pull request:

```sh
composer lint               # Rector, then Pint
composer test               # the dry runs of both, PHPStan at max, 100% type coverage, every suite
composer test:coverage      # 100% line coverage, needs pcov or Xdebug
composer validate --strict  # composer.json against its schema
composer audit --locked     # advisories for the locked packages, needs the network
```

PHPStan and Rector read `src` and `tests` without the fixtures; PHPStan also leaves out
`tests/Pest`. The type coverage reads all of `src` and `tests`. Pint reads the whole tree without
the fixtures.

The code must run on PHP 8.3 to 8.5, with the lowest and the newest versions that `composer.json`
allows, and on macOS.

The checkout of the package keeps the frames of the package in the trace of a failure; a project
hides them unless `HTML_ASSERTIONS_SHOW_FRAMES=1` is set in the shell.

When a message or the output of a check changes, re-record the README's demo and the social preview
with `make -C demo all` (needs Docker).

A workflow uses zizmor to audit the workflows and the Dependabot configuration in `.github`, and
actionlint to check the workflows. Run both when you change anything there:

```sh
docker build --quiet --tag zizmor .github/docker/zizmor
docker run --rm --volume "$PWD:/src:ro" --workdir /src zizmor --offline .github
docker build --quiet --tag actionlint .github/docker/actionlint
docker run --rm --volume "$PWD:/src:ro" --workdir /src actionlint -no-color
```

### Mutation tests

A workflow runs the mutation tests every week. Run them with pcov when you change `src/`. They run
one mutant after another, so run only the files you changed, with `--path`. Do not use
`--parallel`: with it, Pest Mutate counts each mutant as killed.

```sh
composer test:mutation  # fails when the tests kill fewer than 97% of the mutants
composer test:mutation -- --path=src/PHPUnit/Html.php  # only the mutants of that file
```

- `src/Pest/Expectations.php` and `src/PHPUnit/StackTrace.php` are left out. The two `Autoload.php`
  in the `files` of `composer.json` load both classes before Pest Mutate can swap in a mutant, so no
  test could ever see one.
- Most of the other survivors change nothing that a test could see, for example `?? ''` after the
  `localName` of a `DOMElement`, which is never null. One more survives because its test would need
  Livewire, which is not a dependency of the package. A new survivor in your change is either a
  missing test or another such case; say which in the pull request.

## What the package ships

`.gitattributes` excludes everything from the Composer archive except `src/`, `README.md`,
`CHANGELOG.md`, `LICENSE`, `composer.json` and two more:

- `extension.neon`, which `phpstan/extension-installer` loads from `vendor/`;
- `resources/`, where Laravel Boost reads the guideline and the skill.

## Pull requests

### Reporting a bug

Open a pull request that contains only a failing test, with what you expected and the output of
`php -v`. A maintainer adds the fix. If you cannot write the test yourself, describe the problem to
a coding agent and let it draft the pull request; review it before you open it.

### Fixing a bug

Open a pull request with:

- a test that fails without the change;
- the change;
- a description of what went wrong and why the change fixes it, and the output of `php -v`.

### Adding a feature

Open a pull request with tests and a section in the README. Read [Scope](#scope) first.

### Review

A maintainer reviews a pull request once it is ready for review and the `tests` workflow passes; a
bug report without a fix may fail only in the test that it adds. Inactive drafts may be closed.
Pull requests opened in bulk are closed without review.
