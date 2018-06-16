<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Util;

use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

class Workspace
{
    public static function create(): self
    {
        return new Workspace();
    }

    public function init(): self
    {
        self::initWorkspace();
        return $this;
    }

    public function path(string $subPath = '/')
    {
        return Path::join(self::getWorkspacePath(), $subPath);
    }

    public function createFile(string $path, $contents = '')
    {
        $path = $this->path($path);
        $directory = dirname($path);

        if ('.' !== $directory && !file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, $contents);
    }

    public static function getWorkspacePath()
    {
        $path = sys_get_temp_dir() . '/phpbench_test_workspace';

        return $path;
    }

    public static function cleanWorkspace()
    {
        $filesystem = new Filesystem();
        $path = self::getWorkspacePath();
        if (file_exists($path)) {
            $filesystem->remove($path);
        }
    }

    public static function initWorkspace()
    {
        self::cleanWorkspace();
        $filesystem = new Filesystem();
        $filesystem->mkdir(self::getWorkspacePath());
    }
}
