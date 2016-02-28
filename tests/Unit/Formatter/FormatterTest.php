<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Formatter;

use PhpBench\Formatter\Formatter;

class FormatterTest extends \PHPUnit_Framework_TestCase
{
    private $registry;
    private $formatter;
    private $format;

    public function setUp()
    {
        $this->registry = $this->prophesize('PhpBench\Formatter\FormatRegistry');
        $this->formatter = new Formatter($this->registry->reveal());
        $this->format = $this->prophesize('PhpBench\Formatter\FormatInterface');
    }

    /**
     * It should register class definitions.
     * It should apply class definitions.
     */
    public function testApplyClasses()
    {
        $this->registry->get('formatter_1')->willReturn($this->format->reveal());
        $this->formatter->registerClasses(array(
            'one' => array(
                array('formatter_1', array('option_1' => 'value_1')),
            ),
        ));
        $this->format->getDefaultOptions()->willReturn(array(
            'option_1' => 'value_x',
        ));

        $this->format->format('hello world', array('option_1' => 'value_1'))->willReturn('hai!');

        $value = $this->formatter->applyClasses(array('one'), 'hello world');
        $this->assertEquals('hai!', $value);
    }

    /**
     * It should  substitute tokens in class parameters.
     *
     * @dataProvider provideApplyClassesSubstituteTokens
     */
    public function testApplyClassesSubstituteTokens($classParams, $params, $expectedParams)
    {
        $this->registry->get('formatter_1')->willReturn($this->format->reveal());
        $this->formatter->registerClasses(array(
            'one' => array(
                array('formatter_1', $classParams),
            ),
        ));
        $this->format->getDefaultOptions()->willReturn(array(
            'option_1' => 'value_x',
            'option_2' => 'arg',
        ));

        $this->format->format('hello world', $expectedParams)->willReturn('hai!');
        $this->formatter->applyClasses(array('one'), 'hello world', $params);
    }

    public function provideApplyClassesSubstituteTokens()
    {
        return array(
            // replace token.
            array(
                array(
                    'option_1' => '{{ hello }}',
                ),
                array(
                    'hello' => 'foobar',
                ),
                array(
                    'option_1' => 'foobar',
                    'option_2' => 'arg',
                ),
            ),

            // replace token without spaces.
            array(
                array(
                    'option_1' => '{{hello}}',
                ),
                array(
                    'hello' => 'foobar',
                ),
                array(
                    'option_1' => 'foobar',
                    'option_2' => 'arg',
                ),
            ),

            // replace token with parameters.
            array(
                array(
                    'option_1' => '{{hello}}',
                    'option_2' => 'barbar',
                ),
                array(
                    'hello' => 'foobar',
                ),
                array(
                    'option_1' => 'foobar',
                    'option_2' => 'barbar',
                ),
            ),

            // use default value for not-given token.
            array(
                array(
                    'option_1' => '{{ not-given }}',
                    'option_2' => 'barbar',
                ),
                array(
                ),
                array(
                    'option_1' => 'value_x',
                    'option_2' => 'barbar',
                ),
            ),
        );
    }

    /**
     * It should throw an exception if invalid options are given for a formatter.
     */
    public function testInvalidFormatOptions()
    {
        $this->registry->get('formatter_1')->willReturn($this->format->reveal());
        $this->formatter->registerClasses(array(
            'one' => array(
                array('formatter_1', array('not_known' => 'value_1')),
            ),
        ));
        $this->format->getDefaultOptions()->willReturn(array(
            'foobar' => 'barfoo',
        ));

        try {
            $this->formatter->applyClasses(array('one'), 'hello world');
        } catch (\InvalidArgumentException $e) {
            $this->assertNotNull($e->getPrevious());
            $this->assertEquals(
                'Invalid options "not_known" for format "formatter_1", valid options: "foobar"',
                $e->getPrevious()->getMessage()
            );
        }
    }
}
