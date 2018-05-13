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

function param_provider()
{
    return [
        [
            'hello' => 'goodbye',
        ],
    ];
}

class ParameterProviderCallableBench
{
    /**
     * @Groups({"process"})
     * @Iterations(5)
     * @ParamProviders({"param_provider"})
     */
    public function benchParamProviderCallable($params)
    {
    }
}
