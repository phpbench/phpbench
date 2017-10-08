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

namespace PhpBench\Benchmarks\Micro;

class HashLookupBench
{
    private $hashes = [];
    private $hashKeys = [];

    public function generateHashes($params)
    {
        $ret = [];
        $size = 1000;
        $nbHashes = 100;

        for ($i = 0; $i < $nbHashes; $i++) {
            $data = '';
            for ($ii = 0; $ii < $size; $ii++) {
                $data .= chr(rand(47, 127));
            }

            if ('_none_' === $params['algo']) {
                $this->hashes[$data] = 'hello';
                continue;
            }

            $this->hashes[hash($params['algo'], $data)] = 'hello';
        }

        $this->hashKeys = array_keys($this->hashes);
    }

    /**
     * @ParamProviders({"provideAlgos"})
     * @BeforeMethods({"generateHashes"})
     */
    public function benchLookup($params)
    {
        foreach ($this->hashKeys as $key) {
            $this->hashes[$key];
        }
    }

    public function provideAlgos()
    {
        return [
            [
                'algo' => '_none_',
            ],
            [
                'algo' => 'md5',
            ],
            [
                'algo' => 'sha1',
            ],
        ];
    }
}
