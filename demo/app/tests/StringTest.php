<?php

declare(strict_types=1);

it('sells ripe pears', function () {
    expect(cart())->toContain('<li data-name="Pear">Pear, ripe</li>');
});
