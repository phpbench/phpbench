<?php

namespace PhpBench\Tests\Unit\Executor\Method;

use PhpBench\Executor\Method\LocalMethodExecutor;
use PhpBench\Executor\MethodExecutorContext;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LocalMethodExecutorTest extends TestCase
{
    public function testExecutesMethod(): void
    {
        self::assertFalse(LocalTestClass::$executed);


        $this->executeMethod(LocalTestClass::class, 'method');

        self::assertTrue(LocalTestClass::$executed);
    }

    public function testExceptionWhenClassDoesntExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Class "NotExist" does not exist');

        self::assertFalse(LocalTestClass::$executed);

        $this->executeMethod('NotExist', 'method');

        self::assertTrue(LocalTestClass::$executed);
    }

    public function testExceptionWhenMethodDoesntExist(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Method "foobar" on ');

        self::assertFalse(LocalTestClass::$executed);

        $this->executeMethod(LocalTestClass::class, 'foobar');

        self::assertTrue(LocalTestClass::$executed);
    }

    private function executeMethod($className, string $methodName): void
    {
        $context = new MethodExecutorContext(__FILE__, $className);

        (new LocalMethodExecutor())->executeMethods($context, [ $methodName ]);
    }

    protected function tearDown(): void
    {
        LocalTestClass::$executed = false;
    }
}

class LocalTestClass
{
    /**
     * @var bool
     */
    public static $executed = false;

    public function method(): void
    {
        self::$executed = true;
    }
}
