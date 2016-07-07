<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\Metadata;

use BetterReflection\Reflection\ReflectionClass;
use BetterReflection\Reflection\ReflectionMethod;
use PhpBench\Benchmark\Metadata\AnnotationReader;
use PhpBench\Benchmark\Metadata\Annotations;

class AnnotationReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should read class annotations.
     */
    public function testLoadClassMetadata()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');
        $reflection->getDocComment()->willReturn(<<<'EOT'
/**
 * @BeforeClassMethods({"beforeClass"})
 * @AfterClassMethods({"afterClass"})
 */
EOT
        );

        $annotations = $this->createReader()->getClassAnnotations($reflection->reveal());
        $this->assertCount(2, $annotations);
    }

    /**
     * It should read method annotations.
     */
    public function testLoadMethodMetadata()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');
        $reflectionMethod = $this->prophesize(ReflectionMethod::class);
        $reflectionMethod->getName()->willReturn('foobar');
        $reflectionMethod->getDeclaringClass()->willReturn($reflection->reveal());
        $reflectionMethod->getDocComment()->willReturn(<<<'EOT'
/**
 * @Subject()
 * @Iterations(10)
 */
EOT
        );

        $annotations = $this->createReader()->getMethodAnnotations($reflectionMethod->reveal());
        $this->assertCount(2, $annotations);
    }

    /**
     * It should use imported annotations when configured to do so.
     */
    public function testImportedUse()
    {
        $reflection = $this->prophesize(ReflectionClass::class);
        $reflection->getName()->willReturn('Test');
        $reflection->getFileName()->willReturn(__DIR__ . '/classes/Test.php');
        $reflection->getNamespaceName()->willReturn('Foo\Bar');
        $reflectionMethod = $this->prophesize(ReflectionMethod::class);
        $reflectionMethod->getName()->willReturn('method');
        $reflectionMethod->getDeclaringClass()->willReturn($reflection);
        $reflectionMethod->getDocComment()->willReturn(<<<'EOT'
/**
 * @PhpBench\Subject()
 * @PhpBench\Iterations(10)
 */
EOT
        );

        $annotations = $this->createReader(true)->getMethodAnnotations($reflectionMethod->reveal());
        $this->assertCount(2, $annotations);
    }

    private function createReader($useImports = false)
    {
        return new AnnotationReader($useImports);
    }
}
