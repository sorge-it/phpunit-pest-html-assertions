<?php

declare(strict_types=1);

// The screen of the social preview: the name and the claim, the string check that passes, then the
// check by selector that fails, with what it found.

exec('vendor/bin/pest --colors=always tests/StringTest.php', $string);
exec('vendor/bin/pest --colors=always tests/CartTest.php', $cart);

$passed = array_values(array_filter($string, fn (string $line): bool => str_contains($line, 'PASS') || str_contains($line, '✓')));

$failure = [];

foreach ($cart as $line) {
    if ($failure !== [] || str_contains($line, 'FAILED')) {
        $failure[] = $line;
    }

    if (str_contains($line, 'Its text is')) {
        break;
    }
}

echo "\033[H\033[2J\n\n\n\n";
echo "  \033[1;35msorge-it/phpunit-pest-html-assertions\033[0m\n";
echo "  \033[1mYour coding agent's tests are green, and the page is broken.\033[0m\n\n";
echo "  \033[1mtoContain('data-total'), a string check, passes on an empty total:\033[0m\n";
echo implode("\n", $passed)."\n\n";
echo "  \033[1mtoHaveSelectorText('[data-total]', '42.00 EUR'), a check by selector, catches it:\033[0m\n";
echo implode("\n", $failure)."\n";
echo "\033[?25l";
