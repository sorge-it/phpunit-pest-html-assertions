<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Constraint\NotOneNode;

/**
 * Runs one check for the PHPUnit trait and the Pest expectations. The message
 * of the test comes first, as PHPUnit shows it: also before `NotOneNode` and
 * `NotAPage`.
 *
 * @internal
 */
final class Assertion
{
    public static function that(mixed $html, Constraint $constraint, string $message = ''): void
    {
        $page = self::html($html, $message);

        try {
            Assert::assertThat($page, $constraint, $message);
        } catch (NotOneNode $notOneNode) { // @phpstan-ignore catch.neverThrown (the constraint throws it inside assertThat())
            // No `previous`: PHPUnit would print the text and the region a second time.
            throw $message === '' ? $notOneNode : new NotOneNode($message."\n".$notOneNode->getMessage());
        }
    }

    public static function html(mixed $html, string $message = ''): Html
    {
        try {
            return Html::of($html);
        } catch (NotAPage $notaPage) {
            throw $message === '' ? $notaPage : new NotAPage($message."\n".$notaPage->getMessage());
        }
    }
}
