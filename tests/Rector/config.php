<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use SorgeIt\PhpunitPestHtmlAssertions\Rector\CrawlerToHtmlRector;

return RectorConfig::configure()->withRules([CrawlerToHtmlRector::class]);
