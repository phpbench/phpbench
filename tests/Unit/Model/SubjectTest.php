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

namespace PhpBench\Tests\Unit\Subject;

use PhpBench\Model\ParameterSet;
use PhpBench\Tests\TestCase;
use PhpBench\Tests\Util\SubjectBuilder;

class SubjectTest extends TestCase
{
    public function testInGroups(): void
    {
        $subject = SubjectBuilder::create('one')
            ->withGroups(['one', 'two', 'three'])
            ->build();

        self::assertTrue($subject->inGroups(['five', 'two', 'six']));
        self::assertFalse($subject->inGroups(['eight', 'seven']));
        self::assertFalse($subject->inGroups([]));
    }

    /**
     * It should create variants.
     */
    public function testCreateVariant(): void
    {
        $subject = SubjectBuilder::create('one')
            ->build();

        $parameterSet = ParameterSet::fromSerializedParameters('foo', []);
        $variant = $subject->createVariant(
            $parameterSet,
            10,
            20
        );

        self::assertEquals($subject, $variant->getSubject());
        self::assertEquals($parameterSet, $variant->getParameterSet());
        self::assertEquals(0, $variant->count());
        self::assertEquals(10, $variant->getRevolutions());
        self::assertEquals(20, $variant->getWarmup());
    }

    public function testCreateMultipleVariantsWithSameParameterSetName(): void
    {
        $subject = SubjectBuilder::create('one')
            ->build();

        $parameterSet = ParameterSet::fromSerializedParameters('foo', []);
        $variant = $subject->createVariant($parameterSet, 10, 20);
        $parameterSet = ParameterSet::fromSerializedParameters('foo', []);
        $variant = $subject->createVariant($parameterSet, 10, 20);

        self::assertCount(2, $subject->getVariants());
    }

    public function testAddMultipleVariantsWithSameParameterSetName(): void
    {
        $subject = SubjectBuilder::create('one')
            ->variant()
                ->withParameterSet('foo', [])
            ->end()
            ->variant()
                ->withParameterSet('foo', [])
            ->end()
            ->build();

        self::assertCount(2, $subject->getVariants());
    }
}
