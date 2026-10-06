<?php

declare(strict_types=1);

use function SorgeIt\PhpunitPestHtmlAssertions\Pest\html;

it('sells ripe pears', function () {
    expect(html(cart()))
        ->within('[data-cart]')
        ->toHaveSelectorText('[data-name="Pear"]', 'Pear, ripe', 'The shop sells ripe pears only.');
});
