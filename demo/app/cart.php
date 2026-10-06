<?php

declare(strict_types=1);

/** The page of a cart, as a view renders it, with a bug: the total is empty. */
function cart(): string
{
    return <<<'HTML'
        <main>
          <h1>Your cart</h1>
          <ul data-cart>
            <li data-name="Apple">Apple</li>
            <li data-name="Pear">Pear</li>
          </ul>
          <p data-total></p>
        </main>
        HTML;
}
