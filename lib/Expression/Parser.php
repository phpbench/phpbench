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

namespace PhpBench\Expression;

use PhpBench\Expression\Constraint\Comparison;
use PhpBench\Expression\Constraint\Composite;
use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Json\JsonDecoder;

/**
 * Parse a JSON query into a Constraint.
 *
 * Note this query language is based heavily upon the MongoDB
 * query documents:
 *
 * https://docs.mongodb.org/manual/tutorial/query-documents/#select-all-documents-in-a-collection
 *
 * Further modifications should copy the precendent laid down by that implementation.
 */
class Parser
{
    /**
     * @var JsonDecoder
     */
    private $decoder;

    private $comparisons = [
        '$gt', '$lt', '$eq', '$neq', '$gte', '$lte', '$in', '$nin', '$regex',
    ];

    private $composites = [
        '$or', '$and',
    ];

    public function __construct()
    {
        $this->decoder = new JsonDecoder();
    }

    /**
     * @return Constraint
     */
    public function parse($json)
    {
        $expr = $this->decoder->decode($json);

        return $this->processExpr($expr);
    }

    private function processExpr(array $expr)
    {
        if (count($expr) != 1) {
            $newExpr = [];
            foreach ($expr as $key => $value) {
                $newExpr[] = [$key => $value];
            }
            $expr = ['$and' => $newExpr];
        }

        $left = key($expr);
        $right = current($expr);

        if (substr($left, 0, 1) === '$') {
            return $this->parseComposite($left, $right);
        }

        return $this->parseComparison($left, $right);
    }

    private function parseComparison($field, $args)
    {
        if (!is_array($args)) {
            $args = ['$eq' => $args];
        }

        if (count($args) != 1) {
            throw new \InvalidArgumentException(sprintf(
                'Comparisons should be composed of a single key => value pair, got: "%s"',
                json_encode($args)
            ));
        }

        $operator = key($args);
        $value = current($args);

        if (!in_array($operator, $this->comparisons)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown comparison operator, got "%s". Valid operators: "%s"',
                $operator,
                implode('", "', $this->comparisons)
            ));
        }

        return new Comparison($operator, $field, $value);
    }

    private function parseComposite($operator, $args)
    {
        if (!in_array($operator, $this->composites)) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown composite operator, got "%s". Valid operators: "%s"',
                $operator,
                implode('", "', $this->composites)
            ));
        }

        if (count($args) < 2) {
            throw new \InvalidArgumentException(sprintf(
                'Constraints must have at least two arguments, got: "%s"',
                json_encode($args)
            ));
        }

        $leftConstraint = $this->processExpr(array_shift($args));

        $composite = null;

        foreach ($args as $rightArg) {
            $rightConstraint = $this->processExpr($rightArg);
            $composite = new Composite($operator, $leftConstraint, $rightConstraint);

            $leftConstraint = $composite;
        }

        return $composite;
    }
}
