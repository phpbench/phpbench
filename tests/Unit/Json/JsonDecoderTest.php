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

namespace PhpBench\Tests\Unit\Json;

use InvalidArgumentException;
use PhpBench\Json\JsonDecoder;
use PHPUnit\Framework\TestCase;

class JsonDecoderTest extends TestCase
{
    private $jsonDecoder;

    protected function setUp(): void
    {
        $this->jsonDecoder = new JsonDecoder();
    }

    /**
     * It should convert "non-strict" JSON to JSON.
     *
     * @dataProvider provideNormalizer
     */
    public function testNormalizer($string, $expected)
    {
        $result = $this->jsonDecoder->decode($string);
        $this->assertEquals($expected, $result);
    }

    public function provideNormalizer()
    {
        return [
            [
                'iterations: false',
                [
                    'iterations' => false,
                ],
            ],
            [
                'foo: false, bar: true, baz: [ 10, "10", true]',
                [
                    'foo' => false,
                    'bar' => true,
                    'baz' => [10, '10', true],
                ],
            ],
            [
                '{"extends": "aggregate", "foo": ["bar"]}',
                ['extends' => 'aggregate', 'foo' => ['bar']],
            ],
            [
                '{extends: aggregate, foo: ["bar"]}',
                ['extends' => 'aggregate', 'foo' => ['bar']],
            ],
            [
                '{$eq: "bar\"foo"}',
                ['$eq' => 'bar"foo'],
            ],
            [
                '{foobar: "barfoo"}',
                ['foobar' => 'barfoo'],
            ],
            [
                'foobar: "barfoo"',
                ['foobar' => 'barfoo'],
            ],
            [
                'foobar: "barfoo"',
                ['foobar' => 'barfoo'],
            ],
            [
                'foobar": "barfoo"',
                ['foobar' => 'barfoo'],
            ],
            [
                '$and: [ {$gt: {date: "2016-01-30 09:27"}}, {$eq: {subject: "benchMySubject"}}]',
                [
                    '$and' => [
                        [
                            '$gt' => [
                                'date' => '2016-01-30 09:27',
                            ],
                        ],
                        [
                            '$eq' => [
                                'subject' => 'benchMySubject',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'foo: 10',
                [
                    'foo' => 10,
                ],
            ],
            [
                'foo.revs: 1000',
                [
                    'foo.revs' => 1000,
                ],
            ],
            [
                '10: 10',
                [
                    10 => 10,
                ],
            ],
            [
                'foobar[barfoo]: 5',
                ['foobar[barfoo]' => 5],
            ],
            [
                'foo_bar: 5',
                ['foo_bar' => 5],
            ],
            [
                'generator: "table", compare: "subject", compare_fields:[ "mode"], break: ["revs"], cols: ["benchmark"], sort: {"subject:benchGetOptimized:mode": "asc"}',
                [
                    'generator' => 'table',
                    'compare' => 'subject',
                    'compare_fields' => ['mode'],
                    'break' => ['revs'],
                    'cols' => ['benchmark'],
                    'sort' => ['subject:benchGetOptimized:mode' => 'asc'],
                ],
            ],
        ];
    }

    /**
     * It should throw an exception if a non-string value is passed.
     *
     */
    public function testThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a string');
        $this->jsonDecoder->decode(new \stdClass());
    }
}
