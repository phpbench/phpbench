<?php

namespace PhpBench\Runner\Scheduler;

use PhpBench\Runner\Scheduler;

class LinearScheduler implements Scheduler
{
    /**
     * @var Stage[]
     */
    private $stages;

    public function __construct(array $stages)
    {
        $this->stages = $stages;
    }

    public function run()
    {
    }
}
