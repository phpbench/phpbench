<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

use Symfony\Component\Finder\Finder;

class CollectionBuilder
{
    private $finder;
    private $baseDir;

    public function __construct(Finder $finder = null, $baseDir = null)
    {
        $this->finder = $finder ?: new Finder();
        $this->baseDir = $baseDir;
    }

    public function buildCollection($path)
    {
        if ($this->baseDir && '/' !== substr($path, 0, 1)) {
            $path = realpath($this->baseDir . '/' . $path);
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'File or directory "%s" does not exist',
                $path
            ));
        }

        if (is_dir($path)) {
            $this->finder->in($path)
                ->name('*Bench.php');
        } else {
            $this->finder->in(dirname($path))
                ->name(basename($path));
        }

        $cases = array();

        foreach ($this->finder as $file) {
            if (!is_file($file)) {
                continue;
            }

            require_once $file->getRealPath();
            $classFqn = static::getClassNameFromFile($file->getRealPath());
            $refl = new \ReflectionClass($classFqn);

            if (!$refl->isSubclassOf('PhpBench\\BenchmarkInterface')) {
                continue;
            }

            if ($refl->isAbstract()) {
                continue;
            }

            $cases[] = new $classFqn();
        }

        return new Collection($cases);
    }

    /**
     * Return the class name from a file.
     *
     * Taken from http://stackoverflow.com/questions/7153000/get-class-name-from-file
     *
     * @param string $file
     *
     * @return string
     */
    private static function getClassNameFromFile($file)
    {
        $fp = fopen($file, 'r');

        $class = $namespace = $buffer = '';
        $i = 0;

        while (!$class) {
            if (feof($fp)) {
                break;
            }

            $buffer .= fread($fp, 512);
            $tokens = @token_get_all($buffer);

            if (strpos($buffer, '{') === false) {
                continue;
            }

            for (;$i < count($tokens);$i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j = $i + 1;$j < count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace .= '\\' . $tokens[$j][1];
                        } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === T_CLASS) {
                    for ($j = $i + 1;$j < count($tokens);$j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i + 2][1];
                        }
                    }
                }
            }
        };

        if (!$class) {
            return;
        }

        return $namespace . '\\' . $class;
    }
}
