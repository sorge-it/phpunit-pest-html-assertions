<?php

use Symfony\Component\DomCrawler\Crawler;

function page(string $html): void
{
    $count = new Crawler($html)->filter('[data-here]')->count();
    $texts = new Crawler($html)->filter('li')->each(fn (Crawler $node): string => $node->text());
    $names = new Crawler($html)->filter('[data-person]')->each(fn (Crawler $person): ?string => $person->attr('data-name'));
}

?>
-----
<?php

use Symfony\Component\DomCrawler\Crawler;

function page(string $html): void
{
    $count = \SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html::of($html)->count('[data-here]');
    $texts = \SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html::of($html)->texts('li');
    $names = \SorgeIt\PhpunitPestHtmlAssertions\PHPUnit\Html::of($html)->attributes('[data-person]', 'data-name');
}

?>
