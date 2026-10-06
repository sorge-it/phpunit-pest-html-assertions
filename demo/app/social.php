<?php

declare(strict_types=1);

// The screen of the social preview: the name, then the failure without Pest's header.

exec('vendor/bin/pest --colors=always tests/CartTest.php', $lines);

$failure = [];

foreach ($lines as $line) {
    if ($failure !== [] || str_contains($line, 'FAILED')) {
        $failure[] = $line;
    }

    if (str_contains($line, 'Tests:')) {
        break;
    }
}

echo "\033[H\033[2J\n";
echo "  \033[1;35msorge-it/phpunit-pest-html-assertions\033[0m   Check rendered HTML with CSS selectors. Built for coding agents.\n\n";
echo implode("\n", $failure)."\n";
