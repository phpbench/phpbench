<?php

namespace PhpBench\Tests\Unit\Executor\Unit;

use PhpBench\Executor\Unit\CallSubjectUnit;
use PhpBench\Executor\Unit\HrtimeSampler;

class HrtimeSamplerTest extends UnitTestCase
{
    public function testSample(): void
    {
        $result = $this->executeProgram([
            new HrtimeSampler(),
            new CallSubjectUnit(),
        ], $this->context()->build(), [
            'hrtime_sampler',
            [
                'call_subject',
            ]
        ]);
        self::assertCount(1, $this->registeredCalls());
        self::assertIsInt($result['hrtime']['net']);
        self::assertIsInt($result['hrtime']['revs']);
        self::assertEquals('nano', $result['hrtime']['unit']);
    }
}
