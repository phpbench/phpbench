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

namespace PhpBench\Extensions\Dbal\Storage\Driver\Dbal;

use PhpBench\Environment\Information;
use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;

/**
 * This class builds a SuiteCollection from the database for a given query .
 */
class Loader
{
    const BENCHMARKS = 'benchmarks';
    const SUBJECTS = 'subjects';
    const VARIANTS = 'variants';
    const SUITES = 'suites';

    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Load a SuiteCollection for the given query (constraint).
     *
     * @param Constraint $constraint
     *
     * @return SuiteCollection
     */
    public function load(Constraint $constraint)
    {
        $rows = $this->repository->getIterationRows($constraint);

        $context = new \ArrayObject([
            self::BENCHMARKS => [],
            self::SUBJECTS => [],
            self::VARIANTS => [],
            self::SUITES => [],
        ]);

        foreach ($rows as $row) {
            $suite = $this->getSuite($context, $row);
            $benchmark = $this->getBenchmark($context, $suite, $row);
            $subject = $this->getSubject($context, $benchmark, $row);
            $variant = $this->getVariant($context, $subject, $row);
            $iteration = new Iteration(0, $variant, [
                new TimeResult((int) $row['iteration.time']),
                new MemoryResult((int) $row['iteration.memory'], 0, 0),
            ]);
            $variant->addIteration($iteration);
        }

        foreach ($context[self::SUITES] as $suite) {
            foreach ($suite->getVariants() as $variant) {
                $variant->computeStats();
            }
        }

        return new SuiteCollection($context[self::SUITES]);
    }

    private function getBenchmark(\ArrayObject $context, Suite $suite, array $row)
    {
        $key = $row['run.uuid'] . $row['subject.benchmark'];

        if (isset($context[self::BENCHMARKS][$key])) {
            return $context[self::BENCHMARKS][$key];
        }

        $benchmark = $suite->createBenchmark($row['subject.benchmark']);
        $context[self::BENCHMARKS][$key] = $benchmark;

        return $benchmark;
    }

    private function getSubject(\ArrayObject $context, Benchmark $benchmark, $row)
    {
        $key = $row['run.uuid'] . $row['subject.benchmark'] . $row['subject.name'];

        if (isset($context[self::SUBJECTS][$key])) {
            return $context[self::SUBJECTS][$key];
        }

        $subject = $benchmark->createSubject($row['subject.name']);
        $subject->setSleep($row['variant.sleep']);
        $subject->setOutputTimeUnit($row['variant.output_time_unit']);
        $subject->setOutputTimePrecision($row['variant.output_time_precision']);
        $subject->setOutputMode($row['variant.output_mode']);

        $context[self::SUBJECTS][$key] = $subject;

        $groups = $this->repository->getGroups($row['subject.id']);
        $subject->setGroups($groups);

        return $subject;
    }

    private function getVariant(\ArrayObject $context, Subject $subject, $row)
    {
        $key = $row['variant.id'];

        if (isset($context[self::VARIANTS][$key])) {
            return $context[self::VARIANTS][$key];
        }

        $variant = $subject->createVariant(
            new ParameterSet(0, $this->repository->getParameters($row['variant.id'])),
            $row['variant.revolutions'],
            $row['variant.warmup']
        );

        $context[self::VARIANTS][$key] = $variant;

        return $variant;
    }

    private function getSuite(\ArrayObject $context, array $row)
    {
        $key = $row['run.uuid'];
        if (isset($context[self::SUITES][$key])) {
            return $context[self::SUITES][$key];
        }

        $suite = new Suite(
            $row['run.tag'],
            new \DateTime($row['run.date']),
            null,
            [],
            [],
            $row['run.uuid']
        );

        $context[self::SUITES][$key] = $suite;

        $envRows = $this->repository->getRunEnvInformationRows($row['run.id']);

        $providerData = [];
        foreach ($envRows as $row) {
            if (!isset($providerData[$row['provider']])) {
                $providerData[$row['provider']] = [];
            }

            $providerData[$row['provider']][$row['ekey']] = $row['value'];
        }

        $informations = [];
        foreach ($providerData as $name => $data) {
            $informations[] = new Information($name, $data);
        }
        $suite->setEnvInformations($informations);

        return $suite;
    }
}
