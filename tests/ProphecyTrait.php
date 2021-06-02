<?php

namespace PhpBench\Tests;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Prophecy\Exception\Doubler\DoubleException;
use Prophecy\Exception\Doubler\InterfaceNotFoundException;
use Prophecy\Exception\Prediction\PredictionException;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

/**
 * This stub is to permit compatibility with both PHPUnit 8.0 and 9.0 and is
 * mostly lifted from phpspec/prophecy-phpunit.
 */
trait ProphecyTrait
{
    /**
     * @var Prophet|null
     *
     * @internal
     */
    private $prophet;

    /**
     * @var bool
     *
     * @internal
     */
    private $prophecyAssertionsCounted = false;

    /**
     * @throws DoubleException
     * @throws InterfaceNotFoundException
     *
     * @psalm-param class-string|null $type
     */
    protected function prophesize($classOrInterface = null): ObjectProphecy
    {
        if (!is_callable([$this, 'recordDoubledType'])) {
            return parent::prophesize($classOrInterface);
        }

        if (\is_string($classOrInterface)) {
            \assert($this instanceof TestCase);
            $this->recordDoubledType($classOrInterface);
        }

        return $this->getProphet()->prophesize($classOrInterface);
    }

    /**
     * @postCondition
     */
    protected function verifyProphecyDoubles(): void
    {
        if ($this->prophet === null) {
            return;
        }

        try {
            $this->prophet->checkPredictions();
        } catch (PredictionException $e) {
            throw new AssertionFailedError($e->getMessage());
        } finally {
            $this->countProphecyAssertions();
        }
    }

    /**
     * @after
     */
    protected function tearDownProphecy(): void
    {
        if (null !== $this->prophet && !$this->prophecyAssertionsCounted) {
            // Some Prophecy assertions may have been done in tests themselves even when a failure happened before checking mock objects.
            $this->countProphecyAssertions();
        }

        $this->prophet = null;
    }

    /**
     * @internal
     */
    private function countProphecyAssertions(): void
    {
        \assert($this instanceof TestCase);
        $this->prophecyAssertionsCounted = true;

        foreach ($this->prophet->getProphecies() as $objectProphecy) {
            foreach ($objectProphecy->getMethodProphecies() as $methodProphecies) {
                foreach ($methodProphecies as $methodProphecy) {
                    \assert($methodProphecy instanceof MethodProphecy);

                    $this->addToAssertionCount(\count($methodProphecy->getCheckedPredictions()));
                }
            }
        }
    }

    /**
     * @internal
     */
    private function getProphet(): Prophet
    {
        if ($this->prophet === null) {
            $this->prophet = new Prophet();
        }

        return $this->prophet;
    }
}
