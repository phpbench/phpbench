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

namespace PhpBench\Benchmark\Metadata;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\TokenParser;
use PhpBench\Benchmark\Remote\ReflectionClass;
use PhpBench\Benchmark\Remote\ReflectionMethod;

/**
 * Annotation reader.
 */
class AnnotationReader
{
    private $useImports = [];
    private $importUse = false;

    private static $phpBenchImports = [
        'BeforeMethods' => Annotations\BeforeMethods::class,
        'BeforeClassMethods' => Annotations\BeforeClassMethods::class,
        'AfterMethods' => Annotations\AfterMethods::class,
        'AfterClassMethods' => Annotations\AfterClassMethods::class,
        'ParamProviders' => Annotations\ParamProviders::class,
        'Groups' => Annotations\Groups::class,
        'Iterations' => Annotations\Iterations::class,
        'Revs' => Annotations\Revs::class,
        'Skip' => Annotations\Skip::class,
        'Sleep' => Annotations\Sleep::class,
        'OutputTimeUnit' => Annotations\OutputTimeUnit::class,
        'OutputMode' => Annotations\OutputMode::class,
        'Warmup' => Annotations\Warmup::class,
        'Subject' => Annotations\Subject::class,
        'Assert' => Annotations\Assert::class,
        'Executor' => Annotations\Executor::class,
        'Timeout' => Annotations\Timeout::class,
    ];

    private static $globalIgnoredNames = [
        // Annotation tags
        'Annotation' => true, 'Attribute' => true, 'Attributes' => true,
        /* Can we enable this? 'Enum' => true, */
        'Required' => true,
        'Target' => true,
        // Widely used tags (but not existent in phpdoc)
        'fix' => true, 'fixme' => true,
        'override' => true,
        // PHPDocumentor 1 tags
        'abstract' => true, 'access' => true,
        'code' => true,
        'deprec' => true,
        'endcode' => true, 'exception' => true,
        'final' => true,
        'ingroup' => true, 'inheritdoc' => true, 'inheritDoc' => true,
        'magic' => true,
        'name' => true,
        'toc' => true, 'tutorial' => true,
        'private' => true,
        'static' => true, 'staticvar' => true, 'staticVar' => true,
        'throw' => true,
        // PHPDocumentor 2 tags.
        'api' => true,
        'category' => true, 'copyright' => true,
        'deprecated' => true,
        'example' => true,
        'filesource' => true,
        'global' => true,
        'ignore' => true, /* Can we enable this? 'index' => true, */ 'internal' => true,
        'license' => true, 'link' => true,
        'method' => true,
        'package' => true, 'param' => true, 'property' => true, 'property-read' => true, 'property-write' => true,
        'return' => true,
        'see' => true, 'since' => true, 'source' => true, 'subpackage' => true,
        'throws' => true, 'todo' => true, 'TODO' => true,
        'usedby' => true,
        'var' => true, 'version' => true,
        // PHPUnit tags
        'author' => true,
        'after' => true,
        'afterClass' => true,
        'backupGlobals' => true,
        'backupStaticAttributes' => true,
        'before' => true,
        'beforeClass' => true,
        'codeCoverageIgnore' => true,
        'covers' => true,
        'coversDefaultClass' => true,
        'coversNothing' => true,
        'dataProvider' => true,
        'depends' => true,
        'expectedException' => true,
        'expectedExceptionCode' => true,
        'expectedExceptionMessage' => true,
        'expectedExceptionMessageRegExp' => true,
        'group' => true,
        'large' => true,
        'medium' => true,
        'preserveGlobalState' => true,
        'requires' => true,
        'runTestsInSeparateProcesses' => true,
        'runInSeparateProcess' => true,
        'small' => true,
        'test' => true,
        'testdox' => true,
        'ticket' => true,
        'uses' => true,
        // PHPCheckStyle
        'SuppressWarnings' => true,
        // PHPStorm
        'noinspection' => true,
        // PEAR
        'package_version' => true,
        // PlantUML
        'startuml' => true, 'enduml' => true,
    ];

    /**
     * @var DocParser
     */
    private $docParser;

    /**
     * Set import use to true in order to use imported annotations, otherwise
     * import the PHPBench annotations directly.
     *
     * @param bool $importUse
     */
    public function __construct($importUse = false)
    {
        $this->docParser = new DocParser();
        $this->docParser->setIgnoredAnnotationNames(self::$globalIgnoredNames);
        $this->importUse = $importUse;

        AnnotationRegistry::registerLoader(function ($classFqn) {
            if (class_exists($classFqn)) {
                return true;
            }
        });
    }

    /**
     * Return annotations for the given class.
     */
    public function getClassAnnotations(ReflectionClass $class)
    {
        $this->collectImports($class);

        return $this->parse($class->comment, sprintf('benchmark: %s', $class->class));
    }

    public function getMethodAnnotations(ReflectionMethod $method)
    {
        $this->collectImports($method->reflectionClass);

        return $this->parse($method->comment, sprintf('subject %s::%s', $method->class, $method->name));
    }

    private function collectImports(ReflectionClass $class)
    {
        $imports = $this->importUse === true ? $this->getUseImports($class) : $this->getPhpBenchImports();
        $this->docParser->setImports($imports);
    }

    private function getPhpBenchImports()
    {
        static $phpBenchImports;

        if ($phpBenchImports) {
            return $phpBenchImports;
        }

        foreach (self::$phpBenchImports as $key => $value) {
            $phpBenchImports[strtolower($key)] = $value;
        }

        return $phpBenchImports;
    }

    private function getUseImports(ReflectionClass $class)
    {
        if (isset($this->useImports[$class->class])) {
            return $this->useImports[$class->class];
        }

        $content = file_get_contents($class->path);
        $tokenizer = new TokenParser('<?php ' . $content);
        $useImports = $tokenizer->parseUseStatements($class->namespace);
        $this->useImports[$class->class] = $useImports;

        return $useImports;
    }

    /**
     * Delegates to the doctrine DocParser but catches annotation not found errors and throws
     * something useful.
     *
     * @see \Doctrine\Common\Annotations\DocParser
     */
    private function parse($input, $context = '')
    {
        try {
            $annotations = $this->docParser->parse($input, $context);
        } catch (AnnotationException $e) {
            if (!preg_match('/The annotation "(.*)" .* was never imported/', $e->getMessage(), $matches)) {
                throw $e;
            }

            throw new \InvalidArgumentException(sprintf(
                'Unrecognized annotation %s, valid PHPBench annotations: @%s',
                $matches[1],
                implode(', @', array_keys(self::$phpBenchImports))
            ), 0, $e);
        }

        return $annotations;
    }
}
