<?php

namespace PhpBench\Extensions\Elastic\Tests\Unit\Decoder;

use PHPUnit\Framework\TestCase;
use PhpBench\Extensions\Elastic\Encoder\DocumentDecoder;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Model\Suite;
use PhpBench\Extensions\Elastic\Tests\Unit\Encoder\DocumentEncoderTest;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;

class DocumentDecoderTest extends DocumentEncoderTest
{
    /**
     * @var DocumentDecoder
     */
    private $decoder;

    public function setUp()
    {
        parent::setUp();
        $this->decoder = new DocumentDecoder();
    }

    public function testDecoder()
    {
        $exampleSuite = $this->createTestSuite();
        $documents = $this->testEncode();
        $suite = $this->decoder->decode($documents);

        $this->assertEquals($exampleSuite->getContextName(), $suite->getContextName());
        $this->assertEquals($exampleSuite->getDate(), $suite->getDate());
        $this->assertEquals($exampleSuite->getConfigPath(), $suite->getConfigPath());
        $this->assertEquals($exampleSuite->getEnvInformations(), $suite->getEnvInformations());

        $benchmark = $this->firstBenchmark($suite);
        $exampleBenchmark = $this->firstBenchmark($exampleSuite);

        $this->assertEquals($exampleBenchmark->getClass(), $benchmark->getClass());

        $subject = $this->firstSubject($benchmark);
        $exampleSubject = $this->firstSubject($exampleBenchmark);
        $this->assertEquals($exampleSubject->getName(), $subject->getName());
        $this->assertEquals($exampleSubject->getGroups(), $subject->getGroups());
        $this->assertEquals($exampleSubject->getSleep(), $subject->getSleep());

        $variant = $this->firstVariant($subject);
        $exampleVariant = $this->firstVariant($exampleSubject);

        $this->assertEquals($exampleVariant->getParameterSet(), $variant->getParameterSet());
        $this->assertEquals($exampleVariant->getRevolutions(), $variant->getRevolutions());
        $this->assertEquals($exampleVariant->getWarmup(), $variant->getWarmup());

        $exampleVariant->computeStats();
        $variant->computeStats();

        $this->assertEquals(
            $exampleVariant->getStats()->getStats(),
            $variant->getStats()->getStats()
        );
    }

    private function firstBenchmark(Suite $suite): Benchmark
    {
        $this->assertCount(1, $suite->getBenchmarks());
        $benchmarks = $suite->getBenchmarks();
        return reset($benchmarks);
    }

    private function firstSubject(Benchmark $benchmark): Subject
    {
        $subjects = $benchmark->getSubjects();
        $this->assertCount(2, $subjects);
        return reset($subjects);
    }

    private function firstVariant(Subject $subject): Variant
    {
        $variant = $subject->getVariants();
        $this->assertCount(1, $variant);
        return reset($variant);
    }
}
