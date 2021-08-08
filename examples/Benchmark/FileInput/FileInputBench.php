<?php

namespace PhpBench\Examples\Benchmark\Pattern;

// section: all
use Generator;

final class FileInputBench
{
    /**
     * @ParamProviders("provideText")
     */
    public function benchFind(array $params): void
    {
        // do something with $params['text']
    }

    public function provideText(): Generator
    {
        foreach (glob(__DIR__ . '/file-input/*') as $path) {
            yield basename($path) => [
                'text' => file_get_contents($path)
            ];
        }
    }
}
// endsection: all
