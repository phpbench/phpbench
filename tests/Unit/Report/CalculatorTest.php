<?php

namespace PhpBench\Tests\Unit\Report;

use PhpBench\Report\Calculator;

class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should return the sum.
     */
    public function testSum()
    {
        $sum = Calculator::sum(array(30, 3));
        $this->assertEquals(33, $sum);
    }

    /**
     * Sum should accept an XML instance.
     */
    public function testSumDom()
    {
        $dom = new \DOMDocument(1.0);
        $dom->loadXml(<<<EOT
<?xml version="1.0"?>
<elements>
    <element value="10" />
    <element value="20" />
</elements>
EOT
    );

        $xpath = new \DOMXpath($dom);
        $elements = $xpath->query('//element/@value');

        $sum = Calculator::sum($elements);
        $this->assertEquals(30, $sum);
    }

    /**
     * It should return the min.
     */
    public function testMin()
    {
        $min = Calculator::min(array(4, 6, 1, 5));
        $this->assertEquals(1, $min);
    }

    /**
     * It should return the max.
     */
    public function testMax()
    {
        $max = Calculator::max(array(3, 1, 13, 5));
        $this->assertEquals(13, $max);
    }

    /**
     * It should return the average.
     */
    public function testMean()
    {
        $expected = 33 / 7;
        $this->assertEquals($expected, Calculator::mean(array(2, 2, 2, 2, 2, 20, 3)));
    }

    /**
     * Mean should handle no values.
     */
    public function testMeanNoValue()
    {
        $this->assertEquals(0, Calculator::mean(array()));
    }

    /**
     * Mean should return 0 if the sum of all values is zero
     */
    public function testMeanAllZeros()
    {
        $this->assertEquals(0, Calculator::mean(array(0, 0, 0)));
    }

    /**
     * It should return the median of an even set of numbers.
     * The median should be the average between the middle two numbers.
     */
    public function testMedianEven()
    {
        $this->assertEquals(6, Calculator::median(array(9, 5, 7, 3)));
        $this->assertEquals(8, Calculator::median(array(9, 5, 7, 3, 10, 20)));
    }

    /**
     * It should return the median of an odd set of numbers.
     */
    public function testMedianOdd()
    {
        $this->assertEquals(3, Calculator::median(array(10, 3, 3), true));
        $this->assertEquals(3, Calculator::median(array(10, 8, 3, 1, 2), true));
    }

    /**
     * Median should handle no values.
     */
    public function testMedianNoValues()
    {
        $this->assertEquals(0, Calculator::median(array()));
    }

    /**
     * It should throw an exception if the value is not a valid object.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Values passed as an array must be scalar
     */
    public function testSumNonValidObject()
    {
        Calculator::sum(
            array(
                new \stdClass(),
            )
        );
    }

    /**
     * It should provide a deviation as a percentage.
     */
    public function testDeviation()
    {
        $this->assertEquals(0, Calculator::deviation(10, 10));
        $this->assertEquals(100, Calculator::deviation(10, 20));
        $this->assertEquals(-10, Calculator::deviation(10, 9));
        $this->assertEquals(10, Calculator::deviation(10, 11));
        $this->assertEquals(11, Calculator::deviation(0, 11));
        $this->assertEquals(-100, Calculator::deviation(10, 0));
        $this->assertEquals(0, Calculator::deviation(0, 0));
    }
}
