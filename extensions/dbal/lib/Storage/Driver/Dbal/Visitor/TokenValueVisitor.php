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

namespace PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Visitor;

use PhpBench\Expression\Constraint\Comparison;
use PhpBench\Expression\Constraint\Composite;
use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Repository;
use PhpBench\Storage\UuidResolverInterface;

/**
 * Resolves token values, for example "latest" will be resolved to the latest
 * run UUID.
 */
class TokenValueVisitor
{
    /**
     * @var Repository
     */
    private $uuidResolver;

    public function __construct(UuidResolverInterface $uuidResolver)
    {
        $this->uuidResolver = $uuidResolver;
    }

    /**
     * @param Constraint $constraint
     */
    public function visit(Constraint $constraint)
    {
        $this->doVisit($constraint);
    }

    private function doVisit(Constraint $constraint)
    {
        if ($constraint instanceof Comparison) {
            $this->visitComparison($constraint);
        }

        if ($constraint instanceof Composite) {
            $this->visitComposite($constraint);
        }
    }

    /**
     * Replace token values in the comparison values.
     */
    private function visitComparison(Comparison $comparison)
    {
        if ($comparison->getField() === 'run') {
            $this->replaceValue($comparison);
        }
    }

    private function visitComposite(Composite $composite)
    {
        $this->doVisit($composite->getConstraint1());
        $this->doVisit($composite->getConstraint2());
    }

    private function replaceValue(Comparison $comparison)
    {
        $value = $comparison->getValue();
        $isArray = is_array($value);
        $values = (array) $value;

        foreach ($values as &$value) {
            $value = $this->uuidResolver->resolve($value);
        }

        if (false === $isArray) {
            $comparison->replaceValue(reset($values));

            return;
        }

        $comparison->replaceValue($values);
    }
}
