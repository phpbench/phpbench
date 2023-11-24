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

namespace PhpBench\Tests\Unit\Reflection;

use Generator;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Reflection\ReflectionClass;
use PhpBench\Reflection\ReflectionHierarchy;
use PhpBench\Reflection\ReflectionMethod;
use PhpBench\Reflection\ReflectorInterface;
use PhpBench\Reflection\RemoteReflector;
use PhpBench\Remote\Launcher;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Unit\Reflection\reflector\Class1;
use PhpBench\Tests\Unit\Reflection\reflector\Class2;
use PhpBench\Tests\Unit\Reflection\reflector\Class3;

class RemoteReflectorTest extends IntegrationTestCase
{
    /**
     * @var ReflectorInterface
     */
    private $reflector;

    protected function setUp(): void
    {
        $executor = new Launcher(null, null, __DIR__ . '/../../../vendor/autoload.php', null);
        $this->reflector = new RemoteReflector($executor);
    }

    /**
     * It should return information about a class in a different application.
     */
    public function testReflector(): void
    {
        $fname = __DIR__ . '/reflector/ExampleClass.php';
        $classHierarchy = $this->reflector->reflect($fname);
        $this->assertInstanceOf(ReflectionHierarchy::class, $classHierarchy);
        $reflection = $classHierarchy->getTop();
        $this->assertInstanceOf(ReflectionClass::class, $reflection);
        $this->assertEquals('\PhpBench\Tests\Unit\Reflection\reflector\ExampleClass', $reflection->class);
        $this->assertStringContainsString('Some doc comment', $reflection->comment);
        $this->assertEquals($fname, $reflection->path);
        $this->assertEquals([
            'methodOne',
            'methodTwo',
            'provideParamsOne',
            'provideParamsTwo',
            'provideParamsNonScalar',
            'provideParamsNull',
        ], array_keys($reflection->methods));
        $this->assertStringContainsString('Method One Comment', $reflection->methods['methodOne']->comment);
    }

    /**
     * @dataProvider provideReflectAttributes
     */
    public function testReflectAttributes(string $source, callable $assertion): void
    {
        $this->workspace()->put('test.php', $source);

        $classHierarchy = $this->reflector->reflect($this->workspace()->path('test.php'));
        $this->assertInstanceOf(ReflectionHierarchy::class, $classHierarchy);
        $reflection = $classHierarchy->getTop();
        $this->assertInstanceOf(ReflectionClass::class, $reflection);
        assert($reflection instanceof ReflectionClass);
        $assertion($reflection);
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideReflectAttributes(): Generator
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped('PHP 8 only');

            return;
        }

        yield [
            <<<'EOT'
<?php

#[PhpBench\Attributes\Iterations(1)]
class FooBench
{
public function bar(): void
{
}
}
EOT
            , function (ReflectionClass $class): void {
                self::assertCount(1, $class->attributes);
                $first = reset($class->attributes);
                self::assertInstanceof(Iterations::class, $first);
            }
        ];

        yield [
            <<<'EOT'
<?php

#[PhpBench\Attributes\Revs(12)]
class FooBench
{
    public function bar(): void
    {
    }
}
EOT
            , function (ReflectionClass $class): void {
                $first = reset($class->attributes);
                self::assertInstanceof(Revs::class, $first);
                assert($first instanceof Revs);
                self::assertEquals([12], $first->revs);
            }
        ];

        yield [
            <<<'EOT'
<?php

class FooBench
{
    #[PhpBench\Attributes\Iterations(12)]
    public function bar(): void
    {
    }
}
EOT
            , function (ReflectionClass $class): void {
                $method = reset($class->methods);
                assert($method instanceof ReflectionMethod);
                self::assertCount(1, $method->attributes);
                $first = reset($method->attributes);
                self::assertInstanceof(Iterations::class, $first);
                assert($first instanceof Iterations);
                self::assertEquals([12], $first->iterations);
            }
        ];

        yield 'ignores non-existing attributes' => [
            <<<'EOT'
<?php

class FooBench
{
    #[Barzoo(12)]
    public function bar(): void
    {
    }
}
EOT
            , function (ReflectionClass $class): void {
                $method = reset($class->methods);
                assert($method instanceof ReflectionMethod);
                self::assertCount(0, $method->attributes);
            }
        ];
    }

    /**
     * It should return an empty class hierarchy if no classes are found in a file.
     */
    public function testReflectorNoClass(): void
    {
        $fname = __DIR__ . '/reflector/EmptyFile.php';
        $classHierarchy = $this->reflector->reflect($fname);
        $this->assertInstanceOf(ReflectionHierarchy::class, $classHierarchy);
        $this->assertTrue($classHierarchy->isEmpty());
    }

    /**
     * It should parse a file whose class declaration is on the 20th line.
     * See: https://github.com/phpbench/phpbench/issues/325.
     */
    public function testReflector20LineFile(): void
    {
        $fname = __DIR__ . '/reflector/ExampleClass2.php';
        $this->reflector->reflect($fname);
        $this->addToAssertionCount(1);
    }

    /**
     * It should inherit metadata from parent classes.
     */
    public function testHierarchy(): void
    {
        $classHierarchy = $this->reflector->reflect(__DIR__ . '/reflector/Class3.php');
        $this->assertInstanceOf(ReflectionHierarchy::class, $classHierarchy);
        $classHierarchy = iterator_to_array($classHierarchy);
        $reflection = array_shift($classHierarchy);
        $this->assertEquals('\\' . Class3::class, $reflection->class);
        $reflection = array_shift($classHierarchy);
        $this->assertEquals(Class2::class, $reflection->class);
        $reflection = array_shift($classHierarchy);
        $this->assertEquals(Class1::class, $reflection->class);
    }

    /**
     * It should return the parameter sets from a benchmark class.
     */
    public function testGetParameterSets(): void
    {
        $parameterSets = $this->reflector->getParameterSets(__DIR__ . '/reflector/ExampleClass.php', [
            'provideParamsOne',
            'provideParamsTwo',
            'provideParamsNull',
        ]);

        $this->assertEquals([
            [
                [
                    'one' => 'two',
                    'three' => 'four',
                ],
            ],
            [
                [
                    'five' => 'six',
                    'seven' => 'eight',
                ],
            ],
            [
                [
                    'nine' => null,
                    'ten' => null,
                ],
            ],
        ], $parameterSets->toUnserializedParameterSetsCollection());
    }

    /**
     * It should not throw an exception if the parameter set contains non-scalar values.
     */
    public function testGetParameterSetsNonScalar(): void
    {
        $this->reflector->getParameterSets(__DIR__ . '/reflector/ExampleClass.php', [
            'provideParamsNonScalar',
        ]);
        $this->addToAssertionCount(1);
    }

    /**
     * It should parse a class which has multiple class keywords and return the first
     * declared class.
     */
    public function testMultipleClassKeywords(): void
    {
        $fname = __DIR__ . '/reflector/ClassWithClassKeywords.php';
        $classHierarchy = $this->reflector->reflect($fname);
        $reflection = $classHierarchy->getTop();
        $this->assertEquals('\Test\ClassWithClassKeywords', $reflection->class);
    }
}
