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
use PhpBench\Assertion\Ast\Condition;
use PhpBench\Assertion\Ast\Microseconds;
use PhpBench\Assertion\Ast\Operator;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\Unit;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Variable;
use PhpBench\Assertion\Ast\Within;
use Verraes\Parsica\Parser as VerraesParser;
use Verraes\Parsica\StringStream;
use function Verraes\Parsica\alphaChar;
use function Verraes\Parsica\atLeastOne;
use function Verraes\Parsica\char;
use function Verraes\Parsica\collect;
use function Verraes\Parsica\eof;
use function Verraes\Parsica\eol;
use function Verraes\Parsica\float;
use function Verraes\Parsica\integer;
use function Verraes\Parsica\oneOf;
use function Verraes\Parsica\sepBy;
use function Verraes\Parsica\sequence;
use function Verraes\Parsica\string;
use function Verraes\Parsica\stringI;
use function Verraes\Parsica\whitespace;
use function Verraes\Parsica\zeroOrMore;

class Parser
{
    public function parse(string $dsl): Condition
    {
        $predicate = collect(
            $this->parameterParser(),
            whitespace(),
            $this->operatorParser(),
            whitespace(),
            $this->parameterParser(),
            eol()->or(eof())
        )->map(fn (array $vars) => new Condition($vars[0] ,$vars[2], $vars[4]));

        return $predicate->tryString($dsl)->output();
    }

    private function parameterParser(): VerraesParser
    {
        return $this->percentageParser()->or($this->timeValueParser())->or($this->propertyAccessParser());
    }

    private function operatorParser(): VerraesParser
    {
        return $this->withinParser()->or($this->lessThanParser());
    }

    private function withinParser(): VerraesParser
    {
        return collect(
            stringI('within'),
            whitespace(),
            $this->parameterParser(),
            whitespace(),
            stringI('of')
        )->map(fn (array $vars) => new Within($vars[2]));
    }

    private function unitParser(): VerraesParser
    {
        return string('microseconds')
            ->or(string('milliseconds'))
            ->or(string('seconds'))
        ;
    }

    private function lessThanParser(): VerraesParser
    {
        return string('less than')->map(fn (string $operator) => new Comparator($operator));
    }

    private function propertyAccessParser(): VerraesParser
    {
        return sepBy(char('.'), atLeastOne(
            alphaChar()->or(char('_'))
        ))->map(fn (array $segments) => new PropertyAccess($segments));
    }

    private function timeValueParser(): VerraesParser
    {
        return collect(
            float()->or(integer()),
            whitespace()->optional(),
            $this->unitParser(),
        )->map(fn (array $data) => TimeValue::fromValueAndUnit($data[0], $data[2]));
    }

    private function percentageParser(): VerraesParser
    {
        return collect(
            float()->or(integer()),
            whitespace()->optional(),
            string('%')
        )->map(fn (array $data) => new PercentageValue($data[0]));
    }
}
