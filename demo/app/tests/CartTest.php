<?php

declare(strict_types=1);

it('shows the total', function () {
    $html = cart();

    expect($html)->toHaveSelectorText('[data-total]', '42.00 EUR');
});
