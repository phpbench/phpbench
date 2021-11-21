<?php

namespace PhpBench\Tests\Unit\Report\Transform;

use PHPUnit\Framework\TestCase;
use PhpBench\Model\SuiteCollection;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use PhpBench\Tests\Util\SuiteBuilder;

class SuiteCollectionTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $suite = new SuiteCollection([
            SuiteBuilder::create('suite_one')
                ->withDateString('2021-11-21T00:00:00')
                ->benchmark('1st')
                    ->subject('subjectOne')
                        ->withGroups(['one', 'two'])
                        ->variant()
                            ->setRevs(5)
                            ->withParameterSet('one', ['one' => 1, 'two' => 2])
                            ->addIterationWithTimeResult(100, 2)
                        ->end()
                    ->end()
                ->end()
                ->build()

        ]);

        self::assertEquals(
            [
                [
                    'has_baseline' => false,
                    'benchmark_name' => '1st',
                    'benchmark_class' => '1st',
                    'subject_name' => 'subjectOne',
                    'subject_groups' => ['one', 'two'],
                    'subject_time_unit' => 'microseconds',
                    'subject_time_precision' => null,
                    'subject_time_mode' => 'time',
                    'variant_index' => 0,
                    'variant_name' => 'one',
                    'variant_params' => ['one' => 1, 'two' => 2],
                    'variant_revs' => 5,
                    'variant_iterations' => 1,
                    'suite_tag' => 'suite_one',
                    'suite_date' => '2021-11-21',
                    'suite_time' => '00:00:00',
                    'iteration_index' => 0,
                    'result_time_net' => 100,
                    'result_time_revs' => 2,
                    'result_time_avg' => 50,
                    'result_comp_z_value' => 0.0,
                    'result_comp_deviation' => 0.0,
                ]
            ],
            $this->createSuiteTransformer()->suiteToFrame($suite)->toRecords()
        );
    }

    public function testVariantIndexResetForEachSubject(): void
    {
        $collection = new SuiteCollection([
            SuiteBuilder::create('suite_one')
                ->withDateString('2021-11-21T00:00:00')
                ->benchmark('1st')
                    ->subject('subjectOne')
                        ->withGroups(['one', 'two'])
                        ->variant()
                            ->addIterationWithTimeResult(100, 2)
                        ->end()
                        ->variant()
                           ->addIterationWithTimeResult(100, 2)
                        ->end()
                    ->end()
                    ->subject('subjectTwo')
                        ->withGroups(['one', 'two'])
                        ->variant()
                            ->addIterationWithTimeResult(100, 2)
                        ->end()
                        ->variant()
                             ->addIterationWithTimeResult(100, 2)
                        ->end()
                    ->end()
                ->end()
                ->build()

        ]);

        $frame = $this->createSuiteTransformer()->suiteToFrame($collection);
        $records = $frame->toRecords();
        self::assertCount(4, $records);
        self::assertEquals(0, $records[0]['variant_index']);
        self::assertEquals(1, $records[1]['variant_index']);
        self::assertEquals(0, $records[2]['variant_index']);
        self::assertEquals(1, $records[3]['variant_index']);
    }

    private function createSuiteTransformer(): SuiteCollectionTransformer
    {
        return new SuiteCollectionTransformer();
    }
}
