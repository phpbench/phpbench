<?php

namespace PhpBench\Benchmarks\Micro;

class HashBench
{
    /**
     * @ParamProviders({"provideData"})
     */
    public function benchMd5($data)
    {
        hash('md5', $data['data']);
    }

    /**
     * @ParamProviders({"provideData"})
     */
    public function benchSha1($data)
    {
        hash('sha1', $data['data']);
    }

    /**
     * @ParamProviders({"provideData"})
     */
    public function benchSha256($data)
    {
        hash('sha256', $data['data']);
    }

    public function provideData()
    {
        $sizes = [ 10, 100, 1000, 10000, 100000 ];
        $ret = [];

        foreach ($sizes as $size) {
            $data = '';
            for ($i = 0; $i <= $size; $i++) {
                $data .= chr(rand(47, 127));
            }

            $ret[] =[
                'data' => $data
            ];
        }

        return $ret;
    }
}
