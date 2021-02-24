<?php

namespace PhpBench\Tests\Unit\Assertion;

use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\AssertionResult;
use PhpBench\Model\Variant;
use PhpBench\Tests\IntegrationTestCase;

class AssertionProcessorTest extends IntegrationTestCase
{
        /**
         * @dataProvider provideAssert
         */
        public function testAssert(Variant $variant, string $assertion, AssertionResult $expected): void
        {
        }
        
        /**
         * @return Generator<mixed>
         */
        public function provideAssert(): Generator
        {
            yield [
                
            ];
        }
}
