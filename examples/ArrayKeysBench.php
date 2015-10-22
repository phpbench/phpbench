<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\benchmarks;

/**
 * This example benchmarks array_key_exists vs. isset vs. in_array.
 *
 * @BeforeMethods({"init"})
 * @Revs(1000)
 * @Iterations(4)
 * @Groups({"array_keys"})
 * @ParamProviders({"provideNbElements"})
 */
class ArrayKeysBench
{
    private $array;
    private $values;
    private $index = 0;

    public function init($params)
    {
        $this->array = array_fill(0, $params['nb_elements'], 'this is a test');
        $this->values = array_combine(array_keys($this->array), array_keys($this->array));
    }

    public function benchArrayKeyExists()
    {
        array_key_exists($this->index++, $this->array);
    }

    public function benchIsset()
    {
        isset($this->array[$this->index++]);
    }

    public function benchInArray()
    {
        in_array($this->index++, $this->values);
    }

    public function provideNbElements()
    {
        return array(
            array(
                'nb_elements' => 10,
            ),
            array(
                'nb_elements' => 100,
            ),
            array(
                'nb_elements' => 1000,
            ),
            array(
                'nb_elements' => 10000,
            ),
        );
    }
}
