<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Storage\Driver\Xml;

use PhpBench\Expression\Constraint\Comparison;
use PhpBench\Expression\Constraint\Composite;
use PhpBench\Expression\Constraint\Constraint;

/**
 * XML Constraint Visitor.
 *
 * This class needs to provide methods to:
 *
 * 1. Provide criteria with which to reduce the list of XML documents which
 *    need to be loaded -- i.e. it needs to provide a list of explicit dates and run IDs and
 *    lower and upper bounds (date < 2015-01-01, run ID in 1234,4321,1235 etc).
 *
 * 2. Convert the Constraint into an XPath query which can be run against an
 *    aggregate SuiteCollection document.
 */
class ConstraintVisitor
{
    /**
     * Convert the given constraint into an SQL query.
     *
     * @param Constraint $constraint
     *
     * @return string
     */
    public function visit(Constraint $constraint)
    {
        if ($constraint instanceof Comparison) {
            return $this->visitComparison($constraint);
        }

        if ($constraint instanceof Composite) {
            return $this->visitComposite($constraint);
        }
    }

    private function visitComparison(Constraint $constraint)
    {
    }

    private function visitComposite(Constraint $constraint)
    {
    }
}
