<?php

namespace PhpBench\Executor;

class StageRenderer
{
    /**
     * @var array
     */
    private $stages;

    public function __construct(StageInterface ...$stages)
    {
        $this->stages = $stages;
    }
}
