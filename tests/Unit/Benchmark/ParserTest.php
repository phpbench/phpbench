<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Benchmark;

use PhpBench\Benchmark\Parser;

require_once __DIR__ . '/parsertest/ParserCase.php';
require_once __DIR__ . '/parsertest/ParserCaseInvalidAnnotation.php';

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->parser = new Parser();
    }

    /**
     * It should parse all of the bench methods and return anarray of
     * BenchSubject instances.
     *
     * @dataProvider provideParseMethodDoc
     */
    public function testParseMethodDoc($docComment, $expected)
    {
        $result = $this->parser->parseDoc($docComment);
        $this->assertEquals($result, $expected);
    }

    public function provideParseMethodDoc()
    {
        return array(
            array(
                <<<EOT
/**
* @description Hello
* @beforeMethod beforeMe
* @beforeMethod afterBeforeMe
* @paramProvider provideParam
* @iterations  3
* @processIsolation iteration
* @revs 1000
* @revs 10
* @group base
*/
EOT
                ,
                array(
                    'description' => 'Hello',
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'paramProvider' => array('provideParam'),
                    'processIsolation' => 'iteration',
                    'revs' => array(1000, 10),
                    'group' => array('base'),
                ),
            ),
            array(
                <<<EOT
/**
*/
EOT
                ,
                array(
                    'description' => '',
                    'beforeMethod' => array(),
                    'paramProvider' => array(),
                    'iterations' => 1,
                    'processIsolation' => false,
                    'revs' => array(1),
                    'group' => array(),
                ),
            ),
        );
    }

    /**
     * It should thow an exception if an unknown annotation is found.
     *
     * @expectedException \PhpBench\Exception\InvalidArgumentException
     */
    public function testInvalidAnnotation()
    {
        $doc = '/** @asdasd */';
        $this->parser->parseDoc($doc);
    }

    /**
     * It should inherit default values
     * (i.e. each subjet should inherit any annotations given at the class level).
     *
     * @dataProvider provideInheritDefaults
     */
    public function testInheritDefaults($defaults, $annotation, $expected)
    {
        $this->assertEquals($expected, $this->parser->parseDoc($annotation, $defaults));
    }

    public function provideInheritDefaults()
    {
        return array(
            array(
                array(
                    'description' => 'Hello',
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'paramProvider' => array('provideParam'),
                    'processIsolation' => 'iteration',
                    'revs' => array(1000, 10),
                    'group' => array('boo'),
                ),
                <<<EOT
/**
* @description Hallo
* @beforeMethod again
* @paramProvider notherParam
* @iterations 3
* @processIsolation iterations
* @revs 5
* @group five
 */
EOT
                ,
                array(
                    'description' => 'Hallo',
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe', 'again'),
                    'paramProvider' => array('provideParam', 'notherParam'),
                    'processIsolation' => 'iterations',
                    'revs' => array(1000, 10, 5),
                    'group' => array('boo', 'five'),
                ),
            ),
            array(
                array(
                    'description' => 'Hello',
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'paramProvider' => array('provideParam'),
                    'processIsolation' => 'iteration',
                    'revs' => array(1000, 10),
                    'group' => array('boo'),
                ),
                <<<EOT
/**
 * @iterations 4
 */
EOT
                ,
                array(
                    'description' => 'Hello',
                    'iterations' => 4,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'paramProvider' => array('provideParam'),
                    'processIsolation' => 'iteration',
                    'revs' => array(1000, 10),
                    'group' => array('boo'),
                ),
            ),
            array(
                array(
                    'description' => 'Hello',
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'paramProvider' => array('provideParam'),
                    'processIsolation' => 'iteration',
                    'revs' => array(1000, 10),
                ),
                '/** */',
                array(
                    'description' => 'Hello',
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'paramProvider' => array('provideParam'),
                    'processIsolation' => 'iteration',
                    'revs' => array(1000, 10),
                    'group' => array(),
                ),
            ),
        );
    }

    /**
     * It should throw an exception if more than one description annotation is present.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMoreThanOneDescription()
    {
        $doc = <<<EOT
/**
 * @description One
 * @description Two
 */
EOT;
        $this->parser->parseDoc($doc);
    }

    /**
     * It should thow an exception if more than one iterations annotation is present.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMoreThatOneIterationAnnotation()
    {
        $doc = <<<EOT
/**
 * @iterations 2
 * @iterations 2
 */
EOT;
        $this->parser->parseDoc($doc);
    }

    /**
     * Its should throw an exception if the process isolation is not valid.
     *
     * @expectedException PhpBench\Exception\InvalidArgumentException
     * @expectedExceptionMessage Process isolation must be one of "iteration", "iterations"
     */
    public function testInvalidProcessIsolation()
    {
        $doc = <<<EOT
/**
* @description Hello
* @processIsolation iterationasd
*/
EOT
        ;

        $this->parser->parseDoc($doc);
    }
}
