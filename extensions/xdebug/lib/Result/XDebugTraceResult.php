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

namespace PhpBench\Extensions\XDebug\Result;

use Assert\Assertion;
use PhpBench\Dom\Document;
use PhpBench\Model\ResultInterface;

class XDebugTraceResult implements ResultInterface
{
    private $time;
    private $memory;
    private $nbCalls;
    private $traceDocument;

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $values)
    {
        return new self(
            (int) $values['time'],
            (int) $values['memory'],
            (int) $values['nb_calls']
        );
    }

    public function __construct($time, $memory, $nbCalls, Document $traceDocument = null)
    {
        Assertion::integer($time, 'Time is not an integer, got "%s"');
        Assertion::greaterOrEqualThan($time, 0, 'Time must be greater than 0, got "%s"');
        Assertion::integer($memory, 'Memory was not an integer, got "%s"');
        Assertion::greaterOrEqualThan($memory, 0);
        Assertion::integer($nbCalls, 'Number of calls was not an integer, got "%s"');
        Assertion::greaterOrEqualThan($nbCalls, 0);

        $this->time = $time;
        $this->memory = $memory;
        $this->nbCalls = $nbCalls;

        // not serialized.
        $this->traceDocument = $traceDocument;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'xdebug';
    }

    /**
     * {@inheritdoc}
     */
    public function getMetrics()
    {
        return [
            'memory' => $this->memory,
            'time' => $this->time,
            'nb_calls' => $this->nbCalls,
        ];
    }

    public function getTraceDocument()
    {
        return $this->traceDocument;
    }
}
