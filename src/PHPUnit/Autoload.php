<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit;

use Composer\InstalledVersions;

// Composer loads this file in every process that has the package.
StackTrace::hide(
    InstalledVersions::getRootPackage()['install_path'],
    getenv(StackTrace::SHOW_FRAMES) === '1',
);
