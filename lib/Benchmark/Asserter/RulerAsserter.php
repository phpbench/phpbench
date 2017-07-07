<?php

namespace PhpBench\Benchmark\Asserter;

use PhpBench\Benchmark\AsserterInterface;
use Hoa\Ruler\Ruler;
use Hoa\Ruler\Context;
use PhpBench\Math\Distribution;
use PhpBench\Benchmark\Assertion;
use PhpBench\Benchmark\AssertionFailure;

class RulerAsserter implements AsserterInterface
{
    private $ruler;

    public function __construct(Ruler $ruler = null)
    {
        $this->ruler = $ruler ?: new Ruler();
    }

    public function assert(string $expression, Distribution $distribution)
    {
        $context = new Context();
        foreach ($distribution->getStats() as $key => $value) {
            $context[$key] = $value;
        }

        if (false === $this->ruler->assert($expression, $context)) {
            return [ new AssertionFailure($expression) ];
        }

        return [];
    }
}
