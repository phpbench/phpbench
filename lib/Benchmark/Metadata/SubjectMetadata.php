<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Metadata;

/**
 * Metadata for benchmark subjects.
 */
class SubjectMetadata extends AbstractMetadata
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array[]
     */
    private $parameterSets = array();

    /**
     * @var BenchmarkMetadata
     */
    private $benchmarkMetadata;

    /**
     * @var int
     */
    private $index;

    /**
     * @param BenchmarkMetadata $benchmarkMetadata
     * @param string $name
     */
    public function __construct(BenchmarkMetadata $benchmarkMetadata, $name, $index)
    {
        parent::__construct($benchmarkMetadata->getClass());
        $this->name = $name;
        $this->benchmarkMetadata = $benchmarkMetadata;
        $this->index = $index;
    }

    /**
     * Return the method name of this subject.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the parameter sets for this subject.
     *
     * @param array[] $parameterSets
     */
    public function setParameterSets(array $parameterSets)
    {
        $this->parameterSets = $parameterSets;
    }

    /**
     * Return the parameter sets for this subject.
     *
     * @return array[]
     */
    public function getParameterSets()
    {
        return $this->parameterSets;
    }

    /**
     * Return the (containing) benchmark for this subject.
     *
     * @return BenchmarkMetadata
     */
    public function getBenchmarkMetadata()
    {
        return $this->benchmarkMetadata;
    }

    public function getIndex()
    {
        return $this->index;
    }
}
