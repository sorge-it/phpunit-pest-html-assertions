# Contributing

Pull requests are welcome. Issues are closed: a problem comes as a pull request that fixes it.

## A problem

Open a pull request with:

- a test that fails without the change;
- the change;
- a description of what went wrong and why the change fixes it.

If you do not know how to fix it, describe the problem to a coding agent and let it draft the pull
request. Read the change and run the tests before you open it.

In a project, a failure hides the frames of this package; the checkout of the package keeps them. To
see where a problem in your project comes from inside the package, run the test with
`HTML_ASSERTIONS_SHOW_FRAMES=1` set in the shell, for example
`HTML_ASSERTIONS_SHOW_FRAMES=1 vendor/bin/pest`. In `phpunit.xml` it comes too late.

## A feature

Open a pull request with tests and a section in the README. The package checks the HTML a server
renders; see "Not in scope" in the README. A feature outside of that is declined.

## Before you open it

```sh
composer test   # Pint and Rector as a dry run, PHPStan at max, type coverage 100 %, the test suites
composer lint   # Rector, then Pint
```

The code must run on PHP 8.3. When a message or the output of a check changes, record the demo again
with `make -C demo all` (needs Docker).

## Review

A pull request can be reviewed when it is ready for review and `composer test` passes in the CI. A
draft without activity can be closed. Pull requests opened in bulk are closed without review.
