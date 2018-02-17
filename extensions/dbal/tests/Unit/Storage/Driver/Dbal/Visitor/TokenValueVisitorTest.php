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

namespace PhpBench\Tests\Unit\Storage\Driver\Dbal\Visitor;

use PhpBench\Expression\Parser;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Visitor\TokenValueVisitor;
use PhpBench\Storage\UuidResolverInterface;
use PHPUnit\Framework\TestCase;

class TokenValueVisitorTest extends TestCase
{
    private $visitor;
    private $uuidResolver;

    public function setUp()
    {
        $this->uuidResolver = $this->prophesize(UuidResolverInterface::class);
        $this->visitor = new TokenValueVisitor($this->uuidResolver->reveal());
        $this->parser = new Parser();
    }

    /**
     * It should replace "latest" with the latest suite UUID.
     */
    public function testLatest()
    {
        $constraint = $this->parser->parse('run: "latest"');
        $this->uuidResolver->resolve('latest')->willReturn(42);
        $this->visitor->visit($constraint);

        $this->assertEquals(42, $constraint->getValue());
    }

    /**
     * It should not replace values that are not tokens!
     */
    public function testLatestNotReplaceOtherValues()
    {
        $constraint = $this->parser->parse('$and: [ { run: "latest" }, { "run": "foo" } ]');
        $this->uuidResolver->resolve('latest')->willReturn(42);
        $this->uuidResolver->resolve('foo')->willReturn('foo');
        $this->visitor->visit($constraint);

        $constraint1 = $constraint->getConstraint1();
        $constraint2 = $constraint->getConstraint2();
        $this->assertEquals(42, $constraint1->getValue());
        $this->assertEquals('foo', $constraint2->getValue());
    }
}
