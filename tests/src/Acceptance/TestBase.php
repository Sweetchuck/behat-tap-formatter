<?php

declare(strict_types = 1);

namespace Sweetchuck\BehatTapFormatter\Tests\Acceptance;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;

class TestBase extends TestCase
{

    protected static function getProjectRootDir(): string
    {
        return dirname(__DIR__, 3);
    }

    protected static function getFixturesDir(string ...$subDirs): string
    {
        return Path::join(
            static::getProjectRootDir(),
            'tests',
            'fixtures',
            ...$subDirs,
        );
    }
}
