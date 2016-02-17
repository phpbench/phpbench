<?php

namespace PhpBench\Progress\Logger;

use PhpBench\Model\Iteration;
use PhpBench\Model\Variant;

class SimpleLogger extends PhpBenchLogger
{
    public function variantEnd(Variant $variant)
    {
        $subject = $variant->getSubject();
        $benchmark = $subject->getBenchmark();

        $this->output->writeln(sprintf(
            '%s::%s P%s %s', 
            $benchmark->getClass(),
            $subject->getName(),
            $variant->getParameterSet()->getIndex(),
            $this->formatIterationsShortSummary($variant)
        ));
    }
}
