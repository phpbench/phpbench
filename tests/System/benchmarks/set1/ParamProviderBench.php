<?php

class ParamProviderBench
{
    /**
     * @ParamProviders({"provideParams"})
     */
    public function benchSubject(array $params): void
    {
    }

    public static function provideParams()
    {
        yield 'cats' => [
            'cats' => 'two',
        ];

        yield 'dogs' => [
            'dogs' => 'two',
        ];
    }
}
