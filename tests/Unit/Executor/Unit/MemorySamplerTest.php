<?php

namespace PhpBench\Tests\Unit\Executor\Unit;

use PHPUnit\Framework\TestCase;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\Unit\CallSubjectUnit;
use PhpBench\Executor\Unit\HrtimeSampler;
use PhpBench\Executor\Unit\MemorySampler;
use PhpBench\Executor\Unit\RootUnit;

class MemorySamplerTest extends UnitTestCase
{
    public function testSample(): void
    {
        $result = $this->executeProgram([
            new MemorySampler(),
            new CallSubjectUnit(),
        ], $this->context()->build(), [
            'memory_sampler',
        ]);
        self::assertIsInt($result['mem']['final']);
        self::assertIsInt($result['mem']['peak']);
        self::assertIsInt($result['mem']['real']);
    }
}
