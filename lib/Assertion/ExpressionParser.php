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

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast\Argument;
use PhpBench\Assertion\Ast\Comparator;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\Condition;
use PhpBench\Assertion\Ast\Microseconds;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\Operator;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\Unit;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Variable;
use PhpBench\Assertion\Ast\WithinRangeOf;
use Verraes\Parsica\Parser;
use Parsica\StringStream;
use function Verraes\Parsica\alphaChar;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\char;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\float;
use function Verraes\Parsica\integer;
use function Verraes\Parsica\sepBy;
use function Verraes\Parsica\string;
use function Verraes\Parsica\stringI;
use function Verraes\Parsica\whitespace;

class ExpressionParser
{
    public function parse(string $expression): Node
    {
        return 
            $this->withinParser()->or(
                $this->comparisonParser()
            )->tryString($expression)->output();
    }

    private function parameterParser(): Parser
    {
        return $this->percentageParser()
            ->or($this->timeValueParser())
            ->or($this->propertyAccessParser());
    }

    private function comparatorParser(): Parser
    {
        return $this->lessThanParser();
    }

    private function withinParser(): Parser
    {
        return collect(
            $this->parameterParser(),
            whitespace(),
            stringI('within'),
            whitespace(),
            $this->parameterParser(),
            whitespace(),
            stringI('of'),
            whitespace(),
            $this->parameterParser()
        )->map(fn (array $vars) => new WithinRangeOf($vars[0], $vars[4], $vars[8]));
    }

    private function unitParser(): Parser
    {
        return stringI('microseconds')
            ->or(stringI('milliseconds'))
            ->or(stringI('seconds'))
        ;
    }

    private function lessThanParser(): Parser
    {
        return stringI('less than');
    }

    private function propertyAccessParser(): Parser
    {
        return sepBy(char('.'), atLeastOne(
            alphaChar()->or(char('_'))
        ))->map(fn (array $segments) => new PropertyAccess($segments));
    }

    private function timeValueParser(): Parser
    {
        return collect(
            float()->or(integer()),
            whitespace()->optional(),
            $this->unitParser(),
        )->map(fn (array $data) => new TimeValue($data[0], $data[2]));
    }

    private function percentageParser(): Parser
    {
        return collect(
            float()->or(integer()),
            whitespace()->optional(),
            string('%')
        )->map(fn (array $data) => new PercentageValue($data[0]));
    }

    private function comparisonParser(): Parser
    {
        return collect(
            $this->parameterParser(),
            whitespace(),
            $this->comparatorParser(),
            whitespace(),
            $this->parameterParser(),
        )->map(fn (array $vars) => new Comparison($vars[0], $vars[2], $vars[4]));
    }
}
