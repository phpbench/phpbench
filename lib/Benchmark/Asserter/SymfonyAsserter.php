<?php

namespace PhpBench\Benchmark\Asserter;

use PhpBench\Benchmark\AsserterInterface;
use PhpBench\Math\Distribution;
use PhpBench\Benchmark\Assertion;
use PhpBench\Benchmark\AssertionFailure;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class SymfonyAsserter implements AsserterInterface
{
    private $language;

    public function __construct(ExpressionLanguage $language = null)
    {
        $this->language = $language ?: new ExpressionLanguage();
    }

    public function assert(string $expression, Distribution $distribution)
    {
        $context = [
            'stats' => new \stdClass,
        ];
        foreach ($distribution->getStats() as $key => $value) {
            $context['stats']->$key = $value;
        }

        if (false === $this->language->evaluate($expression, $context)) {
            throw new AssertionFailure($expression, $context);
        }
    }
}
