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
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\TokenParser;
use PhpBench\Benchmark\Metadata\Annotations\AfterClassMethods;
use PhpBench\Benchmark\Metadata\Annotations\AfterMethods;
use PhpBench\Benchmark\Metadata\Annotations\Assert;
use PhpBench\Benchmark\Metadata\Annotations\BeforeClassMethods;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\Executor;
use PhpBench\Benchmark\Metadata\Annotations\Format;
use PhpBench\Benchmark\Metadata\Annotations\Groups;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\OutputMode;
use PhpBench\Benchmark\Metadata\Annotations\OutputTimeUnit;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\RetryThreshold;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use PhpBench\Benchmark\Metadata\Annotations\Skip;
use PhpBench\Benchmark\Metadata\Annotations\Sleep;
use PhpBench\Benchmark\Metadata\Annotations\Subject;
use PhpBench\Benchmark\Metadata\Annotations\Timeout;
use PhpBench\Benchmark\Metadata\Annotations\Warmup;
use PhpBench\Benchmark\Metadata\Exception\CouldNotLoadMetadataException;
use PhpBench\Reflection\ReflectionClass;
use PhpBench\Reflection\ReflectionMethod;

/**
 * Annotation reader.
 */
class AnnotationReader
{
    /** @var array<class-string, array<string, class-string>> */
    private array $useImports = [];

    /** @var array<string, class-string> */
    private static array $phpBenchImports = [
        'BeforeMethods' => BeforeMethods::class,
        'BeforeClassMethods' => BeforeClassMethods::class,
        'AfterMethods' => AfterMethods::class,
        'AfterClassMethods' => AfterClassMethods::class,
        'ParamProviders' => ParamProviders::class,
        'Groups' => Groups::class,
        'Iterations' => Iterations::class,
        'Revs' => Revs::class,
        'Skip' => Skip::class,
        'Sleep' => Sleep::class,
        'OutputTimeUnit' => OutputTimeUnit::class,
        'OutputMode' => OutputMode::class,
        'Warmup' => Warmup::class,
        'Subject' => Subject::class,
        'Assert' => Assert::class,
        'Executor' => Executor::class,
        'Timeout' => Timeout::class,
        'Format' => Format::class,
        'RetryThreshold' => RetryThreshold::class,
    ];

    /** @var array<string, bool> */
    private static array $globalIgnoredNames = [
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
        'template' => true,
        'template-covariant' => true,
        'template-contravariant' => true,
        'use' => true,
        'implements' => true,
        'extends' => true,
        'param-out' => true,
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

    private readonly DocParser $docParser;

    /**
     * Set import use to true in order to use imported annotations, otherwise
     * import the PHPBench annotations directly.
     *
     */
    public function __construct(private readonly bool $importUse = false)
    {
        $this->docParser = new DocParser();
        $this->docParser->setIgnoredAnnotationNames(self::$globalIgnoredNames);
    }

    /**
     * Return annotations for the given class.
     *
     * @return list<object>
     */
    public function getClassAnnotations(ReflectionClass $class): array
    {
        $this->collectImports($class);

        return $this->parse($class->comment, sprintf('benchmark: %s', $class->class));
    }

    /**
     * @return list<object>
     */
    public function getMethodAnnotations(ReflectionMethod $method): array
    {
        $this->collectImports($method->reflectionClass);

        return $this->parse($method->comment, sprintf('subject %s::%s', $method->class, $method->name));
    }

    private function collectImports(ReflectionClass $class): void
    {
        $imports = $this->importUse === true ? $this->getUseImports($class) : $this->getPhpBenchImports();
        $this->docParser->setImports($imports);
    }

    /**
     * @return array<string, class-string>
     */
    private function getPhpBenchImports(): array
    {
        /** @var array<string, class-string> $phpBenchImports */
        static $phpBenchImports;

        if ($phpBenchImports) {
            return $phpBenchImports;
        }

        foreach (self::$phpBenchImports as $key => $value) {
            $phpBenchImports[strtolower($key)] = $value;
        }

        return $phpBenchImports;
    }

    /**
     * @return array<string, class-string>
     */
    private function getUseImports(ReflectionClass $class): array
    {
        if (isset($this->useImports[$class->getClass()])) {
            return $this->useImports[$class->getClass()];
        }

        $content = file_get_contents($class->path);
        $tokenizer = new TokenParser('<?php ' . $content);
        /** @var array<string, class-string> $useImports */
        $useImports = $tokenizer->parseUseStatements($class->namespace ?? '');
        $this->useImports[$class->getClass()] = $useImports;

        return $useImports;
    }

    /**
     * Delegates to the doctrine DocParser but catches annotation not found errors and throws
     * something useful.
     *
     * @see DocParser
     *
     * @return list<object>
     */
    private function parse(?string $input, string $context = ''): array
    {
        try {
            $annotations = @$this->docParser->parse($input ?? '', $context);
        } catch (AnnotationException $e) {
            if (!preg_match('/The annotation "(.*)" .* was never imported/', $e->getMessage(), $matches)) {
                throw new CouldNotLoadMetadataException($e->getMessage(), 0, $e);
            }

            throw new CouldNotLoadMetadataException(sprintf(
                'Unrecognized annotation %s, valid PHPBench annotations: @%s',
                $matches[1],
                implode(', @', array_keys(self::$phpBenchImports))
            ), 0, $e);
        }

        return $annotations;
    }
}
