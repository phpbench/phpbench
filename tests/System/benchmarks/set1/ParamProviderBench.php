<?php

class ParamProviderBench
{
    /**
     * @ParamProviders({"provideParams"})
     */
    public function benchSubject(array $params)
    {
    }

    public function provideParams()
    {
        yield [
            'cats' => 'two',
        ];

        yield [
            'dogs' => 'two',
        ];
    }
}
