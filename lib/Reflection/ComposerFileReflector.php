<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Reflection;

use BetterReflection\Reflector\ClassReflector;
use BetterReflection\SourceLocator\Type\ComposerSourceLocator;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Composer\Autoload\ClassLoader;
class ComposerFileReflector extends AbstractFileReflector
{
    private $reflector;

    public function __construct(ClassLoader $classLoader)
    {
        $this->reflector = new ClassReflector(new ComposerSourceLocator($classLoader));
    }

    public function reflectFile($file)
    {
        $class = $this->getClassNameFromFile($file);

        if (null === $reflection = $this->reflector->reflect($class)) {
            $locator = new SingleFileSourceLocator($file);
            $reflector = new ClassReflector($locator);
            $reflection = $reflector->reflect($class);

            if (!$reflection) {
                throw new \InvalidArgumentException(sprintf(
                    'Composer could not find class "%s" for file "%s" and falling back to single-file source location failed.',
                    $class, $file
                ));
            }
        }

        return $reflection;
    }
}
