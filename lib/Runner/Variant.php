<?php

namespace PhpBench\Runner;

class Variant
{
    /**
     * @var string
     */
    public $subjectName;

    /**
     * @var array
     */
    public $parametersSet;

    /**
     * @var int[]
     */
    public $iterations;

    /**
     * @var int[]
     */
    public $revolutions;

    /**
     * @var string
     */
    public $executor;

    /**
     * @var array
     */
    public $executorConfig = [];

    /**
     * @var string
     */
    public $class;

    /**
     * @var int
     */
    public $sleep;

    /**
     * @var string[]
     */
    public $afterMethods = [];

    /**
     * @var string[]
     */
    public $beforeMethods = [];
}
