<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite;

use PhpBench\Expression\Constraint\Comparison;
use PhpBench\Expression\Constraint\Composite;
use PhpBench\Expression\Constraint\Constraint;

/**
 * Converts a Constraint object graph into an SQL query.
 */
class ConstraintVisitor
{
    /**
     * @var int
     */
    private $paramCounter = 0;

    /**
     * @var array
     */
    private $compositeMap = array(
        '$and' => 'AND',
        '$or' => 'OR',
    );

    /**
     * @var array
     */
    private $comparatorMap = array(
        '$eq' => '=',
        '$neq' => '!=',
        '$gt' => '>',
        '$lt' => '<',
        '$gte' => '>=',
        '$lte' => '<=',
        '$in' => null,
        '$regex' => 'REGEXP',
    );

    /**
     * @var array
     */
    private $fieldMap = array(
        'benchmark' => 'subject.benchmark',
        'subject' => 'subject.name',
        'revs' => 'variant.revolutions',
        'date' => 'run.date',
        'run' => 'run.id',
        'group' => 'sgroup.name',
        'param' => 'param',
    );

    /**
     * @var array
     */
    private $values;

    /**
     * Convert the given constraint into an SQL query.
     *
     * @param Constraint $constraint
     *
     * @return string
     */
    public function visit(Constraint $constraint)
    {
        $sql = $this->doVisit($constraint);

        $return = array($sql, $this->values);
        $this->value = array();
        $this->paramCounter = 0;
        $select = array(
            'run.id',
            'run.context',
            'run.date',
            'subject.benchmark',
            'subject.name',
            'subject.id',
            'variant.id',
            'variant.sleep',
            'variant.output_time_unit',
            'variant.output_mode',
            'variant.revolutions',
            'variant.retry_threshold',
            'variant.warmup',
            'iteration.time',
            'iteration.memory',
            'iteration.reject_count',
        );

        $extraJoins = array();
        $fieldNames = $this->getFieldNames($constraint);

        if (in_array('group', $fieldNames)) {
            $extraJoins[] = 'LEFT JOIN sgroup ON sgroup.id = sgroup_subject.sgroup_id';
            $extraJoins[] = 'LEFT JOIN sgroup_subject ON sgroup_subject.subject_id = subject.id';
            $select[] = 'sgroup.name';
        }

        if (in_array('param', $fieldNames)) {
            $extraJoins[] = 'LEFT JOIN variant_parameter ON variant_parameter.variant_id = variant.id';
            $extraJoins[] = 'LEFT JOIN parameter ON variant_parameter.parameter_id = parameter.id';
            $select[] = 'parameter.key';
            $select[] = 'parameter.value';
        }

        $selectSql = <<<'EOT'
SELECT 
    %s
    FROM iteration
    LEFT JOIN variant ON iteration.variant_id = variant.id
    LEFT JOIN subject ON variant.subject_id = subject.id
    LEFT JOIN run ON variant.run_id = run.id
    %s
WHERE

EOT;

        $select = array_map(function ($value) {
            return sprintf('%s AS "%s"', $value, $value);
        }, $select);

        $selectSql = sprintf(
            $selectSql,
            implode(', ', $select),
            implode(' ', $extraJoins)
        );
        $return[0] = $selectSql . $return[0];

        return $return;
    }

    /**
     * Return the field names which can be used in queries.
     *
     * NOTE: This is used for testing and can potentially be removed later with
     *       better tests etc.
     *
     * @see PhpBench\Tests\Functional\Storage\Driver\Sqlite\LoaderTest
     *
     * @return string[]
     */
    public function getValidFieldNames()
    {
        return array_keys($this->fieldMap);
    }

    private function doVisit(Constraint $constraint)
    {
        if ($constraint instanceof Comparison) {
            return $this->visitComparison($constraint);
        }

        if ($constraint instanceof Composite) {
            return $this->visitComposite($constraint);
        }

        throw new \RuntimeException(sprintf(
            'Unsupported constraint class "%s"',
            get_class($constraint)
        ));
    }

    private function visitComparison(Comparison $comparison)
    {
        $fieldName = $comparison->getField();

        if (preg_match('{param\[.*\]}', $fieldName)) {
            $fieldName = 'param';
        }

        if (!isset($this->fieldMap[$fieldName])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown field "%s", allowed fields: "%s"',
                $fieldName, implode('", "', array_keys($this->fieldMap))
            ));
        }

        if (!array_key_exists($comparison->getComparator(), $this->comparatorMap)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported comparator "%s"',
                $comparison->getComparator()
            ));
        }

        $comparator = $comparison->getComparator();
        $fieldName = $this->fieldMap[$fieldName];

        if ($comparator == '$in') {
            return $this->visitComparatorIn($fieldName, $comparison);
        }

        if ($fieldName === 'param') {
            return $this->visitParam($comparison);
        }

        $comparator = $this->getComparatorSql($comparison);
        $paramName = $this->registerParamValue($comparison->getValue());

        return sprintf('%s %s :%s', $fieldName, $comparator, $paramName);
    }

    private function visitParam(Comparison $comparison)
    {
        if (!preg_match('{param\[(.+?)\]}', $comparison->getField(), $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Parameter field "%s" must be of form "param[param_name]"', $comparison->getField()
            ));
        }

        $paramName = $matches[1];

        return sprintf(
            'parameter.key = :%s AND parameter.value %s :%s',
            $this->registerParamValue($paramName),
            $this->getComparatorSql($comparison),
            $this->registerParamValue($comparison->getValue())
        );
    }

    private function visitComparatorIn($fieldName, Comparison $comparison)
    {
        $values = $comparison->getValue();

        if (!is_array($values)) {
            throw new \InvalidArgumentException(sprintf(
                'IN value must be an array, got "%s"',
                gettype($values)
            ));
        }

        $paramNames = array();
        foreach ($values as $value) {
            $paramNames[] = $this->registerParamValue($value);
        }

        return sprintf('%s IN (:%s)', $fieldName, implode(', :', $paramNames));
    }

    private function visitComposite(Composite $composite)
    {
        if (!isset($this->compositeMap[$composite->getOperator()])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown composite operator "%s", allowed "%s"',
                $composite->getOperator(), implode('", "', array_keys($this->compositeMap))
            ));
        }

        return sprintf(
            '(%s %s %s)',
            $this->doVisit($composite->getConstraint1()),
            $this->compositeMap[$composite->getOperator()],
            $this->doVisit($composite->getConstraint2())
        );
    }

    private function getFieldNames(Constraint $constraint)
    {
        $fieldNames = array();

        if ($constraint instanceof Composite) {
            $fieldNames = array_merge($fieldNames, $this->getFieldNames($constraint->getConstraint1()));
            $fieldNames = array_merge($fieldNames, $this->getFieldNames($constraint->getConstraint2()));

            return $fieldNames;
        }

        $field = $constraint->getField();

        if (preg_match('{param\[.*\]}', $field)) {
            $field = 'param';
        }

        return array($field);
    }

    private function registerParamValue($value)
    {
        $paramName = 'param' . $this->paramCounter++;
        $this->values[$paramName] = $value;

        return $paramName;
    }

    private function getComparatorSql(Comparison $comparison)
    {
        return $this->comparatorMap[$comparison->getComparator()];
    }
}
