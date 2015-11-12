<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class IsolatedParameterBench
{
    /**
     * @Groups({"process"})
     * @Iterations(5)
     * @ParamProviders({"provideParams"})
     */
    public function benchIterationIsolation($hello, $goodbye, $goodbye1)
    {
    }

    public function provideParams()
    {
        return array(
            array(
                'hello' => 'Look "I am using double quotes"',
                'goodbye' => 'Look \'I am using single quotes\'"',
                'goodbye1' => 'Look \'I am use $dollars"',
            ),
        );
    }
}
