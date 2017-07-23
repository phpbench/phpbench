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

use PhpBench\Benchmark\Metadata\AnnotationReader;
use PhpBench\Benchmark\Metadata\Annotations;
use PhpBench\Benchmark\Remote\ReflectionClass;
use PhpBench\Benchmark\Remote\ReflectionMethod;
use PHPUnit\Framework\TestCase;

class AnnotationReaderTest extends TestCase
{
    /**
     * It should read class annotations.
     */
    public function testLoadClassMetadata()
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'Test';
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
    public function testLoadMethodMetadata()
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'Test';
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
    public function testImportedUse()
    {
        $reflection = new ReflectionClass();
        $reflection->class = 'Test';
        $reflection->path = __DIR__ . '/classes/Test.php';
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

    private function createReader($useImports = false)
    {
        return new AnnotationReader($useImports);
    }
}
