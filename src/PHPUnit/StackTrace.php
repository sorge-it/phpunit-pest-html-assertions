<?php

declare(strict_types=1);

namespace SorgeIt\PhpunitPestHtmlAssertions\PHPUnit;

use PHPUnit\Util\ExcludeList;

/**
 * @internal
 *
 * A failure points at the line of the test, not into this package. PHPUnit
 * leaves the frames of an excluded directory out of the trace it prints, as
 * it does with its own, and Pest shows the first frame that is left.
 */
final class StackTrace
{
    /** Set to 1 in the shell, the trace keeps the frames of the package: for a bug report. */
    public const string SHOW_FRAMES = 'HTML_ASSERTIONS_SHOW_FRAMES';

    /**
     * The package's own checkout keeps its frames, also in a fork: there they
     * are the code under test. A package without a real path, in a phar, keeps them too.
     */
    public static function hide(string $rootPath, bool $showFrames): void
    {
        $source = realpath(dirname(__DIR__));

        if ($showFrames || $source === false || realpath($rootPath) === dirname($source)) {
            return;
        }

        ExcludeList::addDirectory($source);
    }
}
