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

namespace PhpBench\Benchmark\Remote;

/**
 * Reflector for remote classes.
 */
class Reflector
{
    /**
     * @var Launcher
     */
    private $launcher;

    /**
     * @param Launcher $launcher
     */
    public function __construct(
        Launcher $launcher
    ) {
        $this->launcher = $launcher;
    }

    /**
     * Return an array of ReflectionClass instances for the given file. The
     * first ReflectionClass is the class contained in the given file (there
     * may be only one) additional ReflectionClass instances are the ancestors
     * of this first class.
     *
     * @param string $file
     *
     * @return ReflectionHierarchy
     */
    public function reflect($file)
    {
        $classFqn = $this->getClassNameFromFile($file);
        $hierarchy = new ReflectionHierarchy();

        if (null === $classFqn) {
            return $hierarchy;
        }

        $classHierarchy = $this->launcher->payload(__DIR__ . '/template/reflector.template', [
            'file' => $file,
            'class' => $classFqn,
        ])->launch();

        foreach ($classHierarchy as $classInfo) {
            $reflectionClass = new ReflectionClass();
            $reflectionClass->class = $classInfo['class'];
            $reflectionClass->abstract = $classInfo['abstract'];
            $reflectionClass->comment = $classInfo['comment'];
            $reflectionClass->interfaces = $classInfo['interfaces'];
            $reflectionClass->path = $file;
            $reflectionClass->namespace = $classInfo['namespace'];

            foreach ($classInfo['methods'] as $methodInfo) {
                $reflectionMethod = new ReflectionMethod();
                $reflectionMethod->reflectionClass = $reflectionClass;
                $reflectionMethod->class = $classInfo['class'];
                $reflectionMethod->name = $methodInfo['name'];
                $reflectionMethod->isStatic = $methodInfo['static'];
                $reflectionMethod->comment = $methodInfo['comment'];
                $reflectionClass->methods[$reflectionMethod->name] = $reflectionMethod;
            }
            $hierarchy->addReflectionClass($reflectionClass);
        }

        return $hierarchy;
    }

    /**
     * Return the parameter sets for the benchmark container in the given file.
     *
     * @param string $file
     * @param string[] $paramProviders
     *
     * @return array
     */
    public function getParameterSets($file, $paramProviders)
    {
        $parameterSets = $this->launcher->payload(__DIR__ . '/template/parameter_set_extractor.template', [
            'file' => $file,
            'class' => $this->getClassNameFromFile($file),
            'paramProviders' => var_export($paramProviders, true),
        ])->launch();

        // validate parameters
        $parameters = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($parameterSets));
        iterator_apply($parameters, function (\Iterator $iterator) {
            $parameter = $iterator->current();

            if (!is_scalar($parameter) && isset($parameter)) {
                throw new \InvalidArgumentException(sprintf(
                    'Parameter values must be scalar. Got "%s"',
                    is_object($parameter) ? get_class($parameter) : gettype($parameter)
                ));
            }
        }, [$parameters]);

        return $parameterSets;
    }

    /**
     * Return the class name from a file.
     *
     * Taken from http://stackoverflow.com/questions/7153000/get-class-name-from-file
     */
    private function getClassNameFromFile(string $file): ?string
    {
        $fp = fopen($file, 'r');

        $class = $namespace = $buffer = '';
        $i = 0;

        while (!$class) {
            if (feof($fp)) {
                break;
            }

            // Read entire lines to prevent keyword truncation
            for ($line = 0; $line <= 20; $line++) {
                $buffer .= fgets($fp);
            }
            $tokens = @token_get_all($buffer);

            if (strpos($buffer, '{') === false) {
                continue;
            }

            for (; $i < count($tokens); $i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace .= '\\' . $tokens[$j][1];
                        } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === T_CLASS) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $class = $tokens[$i + 2][1];

                            break 2;
                        }
                    }
                }
            }
        }

        if (!trim($class)) {
            return null;
        }

        return $namespace . '\\' . $class;
    }
}
