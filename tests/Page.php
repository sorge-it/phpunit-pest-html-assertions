<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\Tests;

/** One page for the suites of every part: each check finds something here to pass and something to fail on. */
final class Page
{
    public const string HTML = <<<'HTML'
        <!DOCTYPE html>
        <html>
        <head><title>A page</title><style>.x { color: red }</style></head>
        <body>
            <header data-head>
                <h1>Review on Friday</h1>
                <ul data-people>
                    <li data-name="Anna" class="rounded-lg bg-blue-500">Anna   Example</li>
                    <li data-name="Ben" class="rounded-lg">Ben</li>
                </ul>
            </header>
            <main data-body wire:poll.10s="refresh">
                <p data-text>Line one
        Line two</p>
                <p data-note>Review confirmed<script>alert(1)</script></p>
                <a href="/out?u=1" data-link>Next</a>
                <span data-empty> </span>
                <form>
                    <input name="email" value="anna@example.test">
                    <textarea name="note">Please check the café</textarea>
                    <select name="day" data-day><option value="mo">Montag</option><option value="fr" selected>Freitag</option></select>
                    <input type="checkbox" name="agree" data-agree checked>
                    <fieldset disabled><button data-send>Senden</button></fieldset>
                    <button data-cancel>Abbrechen</button>
                </form>
                <iframe data-mail srcdoc="&lt;p data-mail-text&gt;Hello&lt;/p&gt;"></iframe>
            </main>
        </body>
        </html>
        HTML;
}
