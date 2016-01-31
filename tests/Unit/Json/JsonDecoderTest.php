<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Json;

use PhpBench\Json\JsonDecoder;

class JsonDecoderTest extends \PHPUnit_Framework_TestCase
{
    private $jsonDecoder;

    public function setUp()
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
        return array(
            array(
                '{$eq: "barfoo"}',
                array('$eq' => 'barfoo'),
            ),
            array(
                '{foobar: "barfoo"}',
                array('foobar' => 'barfoo'),
            ),
            array(
                'foobar: "barfoo"',
                array('foobar' => 'barfoo'),
            ),
            array(
                'foobar: "barfoo"',
                array('foobar' => 'barfoo'),
            ),
            array(
                'foobar": "barfoo"',
                array('foobar' => 'barfoo'),
            ),
            array(
                '$and: [ {$gt: {date: "2016-01-30 09:27"}}, {$eq: {subject: "benchMySubject"}}]',
                array(
                    '$and' => array(
                        array(
                            '$gt' => array(
                                'date' => '2016-01-30 09:27',
                            ),
                        ),
                        array(
                            '$eq' => array(
                                'subject' => 'benchMySubject',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'foo: 10',
                array(
                    'foo' => 10,
                ),
            ),
            array(
                '10: 10',
                array(
                    10 => 10,
                ),
            ),
        );
    }

    /**
     * It should throw an exception if a non-string value is passed.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Expected a string
     */
    public function testThrowException()
    {
        $this->jsonDecoder->decode(new \stdClass());
    }
}
