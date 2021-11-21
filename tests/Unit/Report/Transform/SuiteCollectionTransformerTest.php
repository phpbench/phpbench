<?php

namespace PhpBench\Tests\Unit\Report\Transform;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PhpBench\Data\DataFrame;
use PhpBench\Model\Result\TimeResult;
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
                            ->iteration()
                                ->setResult(new TimeResult(100, 2))
                            ->end()
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
            (new SuiteCollectionTransformer())->suiteToFrame($suite)->toRecords()
        );
    }
}
