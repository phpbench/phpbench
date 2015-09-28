<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Benchmark;

use PhpBench\Benchmark\Parser;

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
        $this->assertEquals($expected, $result);
    }

    public function provideParseMethodDoc()
    {
        return array(
            array(
                <<<EOT
/**
* @beforeMethod beforeMe
* @afterMethod afterMe
* @afterMethod afterAfterMe
* @beforeMethod afterBeforeMe
* @paramProvider provideParam
* @iterations  3
* @revs 1000
* @revs 10
* @group base
* @skip
*/
EOT
                , array(
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'afterMethod' => array('afterMe', 'afterAfterMe'),
                    'paramProvider' => array('provideParam'),
                    'revs' => array(1000, 10),
                    'group' => array('base'),
                    'skip' => true,
                ),
            ),
            array(
                <<<EOT
/**
* @skip Not implemented
*/
EOT
                , array(
                    'iterations' => 1,
                    'beforeMethod' => array(),
                    'afterMethod' => array(),
                    'paramProvider' => array(),
                    'revs' => array(),
                    'group' => array(),
                    'skip' => true,
                ),
            ),
            array(
                <<<EOT
/**
*/
EOT
                ,
                array(
                    'beforeMethod' => array(),
                    'afterMethod' => array(),
                    'paramProvider' => array(),
                    'iterations' => 1,
                    'revs' => array(),
                    'group' => array(),
                    'skip' => false,
                ),
            ),
        );
    }

    /**
     * It should thow an exception if an unknown annotation is found.
     *
     * @expectedException InvalidArgumentException
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
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'afterMethod' => array(),
                    'paramProvider' => array('provideParam'),
                    'revs' => array(1000, 10),
                    'group' => array('boo'),
                    'skip' => false,
                ),
                <<<EOT
/**
* @beforeMethod again
* @paramProvider notherParam
* @iterations 3
* @revs 5
* @group five
 */
EOT
                ,
                array(
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe', 'again'),
                    'afterMethod' => array(),
                    'paramProvider' => array('provideParam', 'notherParam'),
                    'revs' => array(1000, 10, 5),
                    'group' => array('boo', 'five'),
                    'skip' => false,
                ),
            ),
            array(
                array(
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'afterMethod' => array(),
                    'paramProvider' => array('provideParam'),
                    'revs' => array(1000, 10),
                    'group' => array('boo'),
                    'skip' => false,
                ),
                <<<EOT
/**
 * @iterations 4
 */
EOT
                ,
                array(
                    'iterations' => 4,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'afterMethod' => array(),
                    'paramProvider' => array('provideParam'),
                    'revs' => array(1000, 10),
                    'group' => array('boo'),
                    'skip' => false,
                ),
            ),
            array(
                array(
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'afterMethod' => array(),
                    'paramProvider' => array('provideParam'),
                    'revs' => array(1000, 10),
                    'skip' => false,
                ),
                '/** */',
                array(
                    'iterations' => 3,
                    'beforeMethod' => array('beforeMe', 'afterBeforeMe'),
                    'afterMethod' => array(),
                    'paramProvider' => array('provideParam'),
                    'revs' => array(1000, 10),
                    'group' => array(),
                    'skip' => false,
                ),
            ),
        );
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
}
