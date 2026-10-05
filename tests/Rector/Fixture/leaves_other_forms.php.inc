<?php

use Symfony\Component\DomCrawler\Crawler;

function page(string $html, Crawler $crawler, \DOMNode $node): void
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
}

?>
