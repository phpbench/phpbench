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

use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\WithinRangeOf;
use function Verraes\Parsica\alphaChar;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\char;
use function Verraes\Parsica\charI;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\eof;
use function Verraes\Parsica\float;
use function Verraes\Parsica\integer;
use Verraes\Parsica\Parser;
use function Verraes\Parsica\keepFirst;
use function Verraes\Parsica\sepBy2;
use function Verraes\Parsica\sequence;
use function Verraes\Parsica\string;
use function Verraes\Parsica\stringI;
use function Verraes\Parsica\whitespace;

class ExpressionParser
{
    public function parse(string $expression): Node
    {
        return keepFirst(
            $this->expressionParser(),
            eof()
        )->tryString($expression)->output();
    }

    private function expressionParser(): Parser
    {
        return $this->withinParser()->or(
            $this->comparisonParser()
        );
    }

    private function valueParser(): Parser
    {
        return $this->timeValueParser()
            ->or($this->memoryParser())
            ->or($this->propertyAccessParser())
            ->or($this->throughputParser());
    }

    private function comparatorParser(): Parser
    {
        return $this->lessThanParser();
    }

    private function withinParser(): Parser
    {
        return collect(
            $this->valueParser(),
            whitespace(),
            stringI('within'),
            whitespace(),
            $this->percentageParser()->or($this->valueParser()),
            whitespace(),
            stringI('of'),
            whitespace(),
            $this->valueParser(),
            whitespace()->optional(),
            $this->toleranceParser()->optional()
        )->map(fn (array $vars) => new WithinRangeOf($vars[0], $vars[4], $vars[8]));
    }

    private function timeUnitParser(): Parser
    {
        return stringI('microseconds')
            ->or(stringI('milliseconds'))
            ->or(stringI('seconds'))
        ;
    }

    private function memoryUnitParser(): Parser
    {
        return stringI('bytes')
            ->or(stringI('kilobytes'))
            ->or(stringI('megabytes'))
            ->or(stringI('gigabytes'));
    }

    private function lessThanParser(): Parser
    {
        return string('<=')
            ->or(char('<'))
            ->or(char('='))
            ->or(string('>='))
            ->or(char('>'));
    }

    private function propertyAccessParser(): Parser
    {
        return sepBy2(char('.'), atLeastOne(
            alphaChar()->or(char('_'))
        ))->map(fn (array $segments) => new PropertyAccess($segments));
    }

    private function timeValueParser(): Parser
    {
        return collect(
            float()->or(integer()),
            whitespace()->optional(),
            $this->timeUnitParser(),
        )->map(fn (array $data) => new TimeValue($data[0], $data[2]));
    }

    private function memoryParser(): Parser
    {
        return collect(
            float()->or(integer()),
            whitespace()->optional(),
            $this->memoryUnitParser(),
        )->map(fn (array $data) => new MemoryValue($data[0], $data[2]));
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
            $this->valueParser(),
            whitespace(),
            $this->comparatorParser(),
            whitespace(),
            $this->valueParser(),
            whitespace()->optional(),
            $this->toleranceParser()->optional()
        )->map(fn (array $vars) => new Comparison($vars[0], $vars[2], $vars[4], $vars[6]));
    }

    private function toleranceParser(): Parser
    {
        return collect(
            string('+/-')->or(char('±')),
            whitespace()->optional(),
            $this->percentageParser()->or($this->valueParser()),
        )->map(fn (array $vars) => $vars[2]);
    }

    private function throughputParser(): Parser
    {
        return collect(
            float()->or(integer()),
            whitespace(),
            stringI('ops/'),
            stringI('microsecond')
                ->or(stringI('second'))
                ->or(stringI('millisecond'))
                ->or(stringI('second'))
        )->map(fn (array $vars) => new ThroughputValue($vars[0], $vars[3]));
    }
}
