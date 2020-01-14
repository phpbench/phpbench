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

namespace PhpBench\Tests\Unit\Formatter;

use PhpBench\Formatter\FormatInterface;
use PhpBench\Formatter\FormatRegistry;
use PhpBench\Formatter\Formatter;
use PHPUnit\Framework\TestCase;

class FormatterTest extends TestCase
{
    private $registry;
    private $formatter;
    private $format;

    protected function setUp(): void
    {
        $this->registry = $this->prophesize(FormatRegistry::class);
        $this->formatter = new Formatter($this->registry->reveal());
        $this->format = $this->prophesize(FormatInterface::class);
    }

    /**
     * It should register class definitions.
     * It should apply class definitions.
     */
    public function testApplyClasses()
    {
        $this->registry->get('formatter_1')->willReturn($this->format->reveal());
        $this->formatter->registerClasses([
            'one' => [
                ['formatter_1', ['option_1' => 'value_1']],
            ],
        ]);
        $this->format->getDefaultOptions()->willReturn([
            'option_1' => 'value_x',
        ]);

        $this->format->format('hello world', ['option_1' => 'value_1'])->willReturn('hai!');

        $value = $this->formatter->applyClasses(['one'], 'hello world');
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
        $this->formatter->registerClasses([
            'one' => [
                ['formatter_1', $classParams],
            ],
        ]);
        $this->format->getDefaultOptions()->willReturn([
            'option_1' => 'value_x',
            'option_2' => 'arg',
        ]);

        $this->format->format('hello world', $expectedParams)->shouldBeCalled()->willReturn('hai!');
        $this->formatter->applyClasses(['one'], 'hello world', $params);
    }

    public function provideApplyClassesSubstituteTokens()
    {
        return [
            // replace token.
            [
                [
                    'option_1' => '{{ hello }}',
                ],
                [
                    'hello' => 'foobar',
                ],
                [
                    'option_1' => 'foobar',
                    'option_2' => 'arg',
                ],
            ],

            // replace token without spaces.
            [
                [
                    'option_1' => '{{hello}}',
                ],
                [
                    'hello' => 'foobar',
                ],
                [
                    'option_1' => 'foobar',
                    'option_2' => 'arg',
                ],
            ],

            // replace token with parameters.
            [
                [
                    'option_1' => '{{hello}}',
                    'option_2' => 'barbar',
                ],
                [
                    'hello' => 'foobar',
                ],
                [
                    'option_1' => 'foobar',
                    'option_2' => 'barbar',
                ],
            ],

            // use default value for not-given token.
            [
                [
                    'option_1' => '{{ not-given }}',
                    'option_2' => 'barbar',
                ],
                [
                ],
                [
                    'option_1' => 'value_x',
                    'option_2' => 'barbar',
                ],
            ],
        ];
    }

    /**
     * It should throw an exception if invalid options are given for a formatter.
     */
    public function testInvalidFormatOptions()
    {
        $this->registry->get('formatter_1')->willReturn($this->format->reveal());
        $this->formatter->registerClasses([
            'one' => [
                ['formatter_1', ['not_known' => 'value_1']],
            ],
        ]);
        $this->format->getDefaultOptions()->willReturn([
            'foobar' => 'barfoo',
        ]);

        try {
            $this->formatter->applyClasses(['one'], 'hello world');
        } catch (\InvalidArgumentException $e) {
            $this->assertNotNull($e->getPrevious());
            $this->assertEquals(
                'Invalid options "not_known" for format "formatter_1", valid options: "foobar"',
                $e->getPrevious()->getMessage()
            );
        }
    }
}
