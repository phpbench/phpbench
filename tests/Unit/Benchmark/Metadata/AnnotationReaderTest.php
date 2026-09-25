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

namespace PhpBench\Tests\Unit\Benchmark\Metadata;

use Generator;
use stdClass;
use PhpBench\Benchmark\Metadata\AnnotationReader;
use PhpBench\Benchmark\Metadata\Annotations;
use PhpBench\Benchmark\Metadata\Exception\CouldNotLoadMetadataException;
use PhpBench\Reflection\ReflectionClass;
use PhpBench\Reflection\ReflectionMethod;
use PhpBench\Tests\TestCase;

class AnnotationReaderTest extends TestCase
{
    /**
     * It should read class annotations.
     */
    public function testLoadClassMetadata(): void
    {
        $reflection = new ReflectionClass();
        $reflection->class = stdClass::class;
        $reflection->comment = <<<'EOT'
/**
 * @BeforeClassMethods({"beforeClass"})
 * @AfterClassMethods({"afterClass"})
 */
EOT;

        $annotations = $this->createReader()->getClassAnnotations($reflection);
        $this->assertCount(2, $annotations);
    }

    /**
     * It should read method annotations.
     */
    public function testLoadMethodMetadata(): void
    {
        $reflection = new ReflectionClass();
        $reflection->class = stdClass::class;
        $reflectionMethod = new ReflectionMethod();
        $reflectionMethod->reflectionClass = $reflection;
        $reflectionMethod->comment = <<<'EOT'
/**
 * @Subject()
 * @Iterations(10)
 */
EOT;

        $annotations = $this->createReader()->getMethodAnnotations($reflectionMethod);
        $this->assertCount(2, $annotations);
    }

    /**
     * It should use imported annotations when configured to do so.
     */
    public function testImportedUse(): void
    {
        $reflection = new ReflectionClass();
        $reflection->class = stdClass::class;
        $reflection->path = __DIR__ . '/classes/TestAnnotation.php';
        $reflectionMethod = new ReflectionMethod();
        $reflectionMethod->reflectionClass = $reflection;
        $reflectionMethod->comment = <<<'EOT'
/**
 * @PhpBench\Subject()
 * @PhpBench\Iterations(10)
 */
EOT;

        $annotations = $this->createReader(true)->getMethodAnnotations($reflectionMethod);
        $this->assertCount(2, $annotations);
    }

    /**
     * @dataProvider provideAnnotationsWithoutDoctrine
     */
    public function testThrowsOnPhpBenchAnnotationWithoutDoctrine(string $comment): void
    {
        $this->expectException(CouldNotLoadMetadataException::class);
        $this->expectExceptionMessage('doctrine/annotations');

        $this->createReader(false, false)->getClassAnnotations($this->createClass($comment));
    }

    /**
     * @return Generator<string, array{string}>
     */
    public static function provideAnnotationsWithoutDoctrine(): Generator
    {
        yield 'leading tag' => ["/**\n * @Revs(10)\n */"];

        yield 'lower case' => ["/**\n * @revs(10)\n */"];

        yield 'mixed case' => ["/**\n * @outputTimeUnit(\"seconds\")\n */"];

        yield 'inline' => ['/** Measures hashing. @Iterations(5) */'];

        yield 'line without asterisk' => ["/**\n    @Iterations(5)\n */"];

        yield 'fully qualified' => ["/**\n * @PhpBench\\Benchmark\\Metadata\\Annotations\\Revs(10)\n */"];
    }

    /**
     * @dataProvider provideForeignAnnotationsWithoutDoctrine
     */
    public function testIgnoresForeignAnnotationsWithoutDoctrine(string $comment): void
    {
        self::assertSame([], $this->createReader(false, false)->getClassAnnotations($this->createClass($comment)));
    }

    /**
     * @return Generator<string, array{string}>
     */
    public static function provideForeignAnnotationsWithoutDoctrine(): Generator
    {
        yield 'no annotations' => ["/**\n * Just a description.\n */"];

        yield 'phpdoc tags' => ["/**\n * @param string \$foo\n * @group foo\n */"];

        yield 'foreign namespace segment' => ["/**\n * @Assert\\NotBlank\n */"];

        yield 'foreign class with same name' => ["/**\n * @Serializer\\Groups({\"a\"})\n */"];

        yield 'longer name' => ["/**\n * @RevsTotal(10)\n */"];

        yield 'email address' => ["/**\n * Contact foo@revs.example\n */"];
    }

    public function testThrowsOnImportedAnnotationsWithoutDoctrine(): void
    {
        $this->expectException(CouldNotLoadMetadataException::class);
        $this->expectExceptionMessage('imports PHPBench annotations');

        $reflection = $this->createClass("/**\n * Nothing here.\n */");
        $reflection->path = __DIR__ . '/classes/TestAnnotationAlias.php';

        $this->createReader(true, false)->getClassAnnotations($reflection);
    }

    public function testIgnoresClassWithoutImportsWithoutDoctrine(): void
    {
        $reflection = $this->createClass("/**\n * Nothing here.\n */");
        $reflection->path = __DIR__ . '/BenchmarkMetadataTest.php';

        self::assertSame([], $this->createReader(true, false)->getClassAnnotations($reflection));
    }

    private function createClass(string $comment): ReflectionClass
    {
        $reflection = new ReflectionClass();
        $reflection->class = stdClass::class;
        $reflection->comment = $comment;

        return $reflection;
    }

    private function createReader(bool $useImports = false, ?bool $doctrineAvailable = null): AnnotationReader
    {
        return new AnnotationReader($useImports, $doctrineAvailable);
    }
}
