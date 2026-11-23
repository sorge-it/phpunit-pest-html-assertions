<?php

use Symfony\Component\DomCrawler\Crawler;

function page(string $html, Crawler $crawler, \DOMNode $node, string $method, string $name): void
{
    // Not a new crawler of a page: left alone.
    $count = $crawler->filter('li')->count();
    // A cast changes null into '': not the list of attributes.
    $names = new Crawler($html)->filter('[data-person]')->each(fn (Crawler $person): string => (string) $person->attr('data-name'));
    // Text without normalised white space: not the texts.
    $raw = new Crawler($html)->filter('p')->each(fn (Crawler $node): string => $node->text(null, false));
    // A node, not a page: Html reads no node.
    $nodes = new Crawler($node)->filter('li')->count();
    // Another class of the same short name.
    $other = new \App\Crawler($html)->filter('li')->count();
    // Another call than filter() before the count.
    $paths = new Crawler($html)->filterXPath('//li')->count();
    // Another call than count() or each() after filter().
    $first = new Crawler($html)->filter('li')->first();
    $kept = new Crawler($html)->filter('li')->reduce(fn (Crawler $node): string => $node->text());
    // A closure instead of an arrow function.
    $long = new Crawler($html)->filter('li')->each(function (Crawler $node): string {
        return $node->text();
    });
    // A method named by a variable.
    $called = new Crawler($html)->filter('li')->{$method}();
    $found = new Crawler($html)->{$method}('li')->count();
    $read = new Crawler($html)->filter('li')->each(fn (Crawler $node): string => $node->{$method}());
    // A first-class callable.
    $counter = new Crawler($html)->filter('li')->count(...);
    $filter = new Crawler($html)->filter(...)->count();
    // An attribute with a default, or named by a variable: not the list of attributes.
    $defaults = new Crawler($html)->filter('[data-person]')->each(fn (Crawler $person): string => $person->attr('data-name', ''));
    $values = new Crawler($html)->filter('[data-person]')->each(fn (Crawler $person): ?string => $person->attr($name));
    // Another read of each node.
    $links = new Crawler($html)->filter('li')->each(fn (Crawler $node): Crawler => $node->filter('a'));
}

?>
