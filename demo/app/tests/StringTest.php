<?php

declare(strict_types=1);

it('shows the total', function () {
    $html = cart();

    expect($html)->toContain('data-total');
});
