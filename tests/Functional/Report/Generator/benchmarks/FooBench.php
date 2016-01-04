<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class FooBench
{
    /**
     * @Groups({"one", "two", "three"})
     * @ParamProviders({"provideParams"})
     * @OutputTimeUnit("microseconds")
     * @Revs(5)
     * @Iterations(2)
     */
    public function benchMySubject($params)
    {
    }

    public function benchOtherSubject()
    {
    }

    public function provideParams()
    {
        return array(
            array(
                'foo' => 'bar',
                'array' => array(
                    'one',
                    'two',
                ),
                'assoc_array' => array(
                    'one' => 'two',
                    'three' => 'four',
                ),
            ),
        );
    }
}
