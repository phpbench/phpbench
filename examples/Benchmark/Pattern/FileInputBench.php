<?php

namespace PhpBench\Examples\Benchmark\Pattern;

use Generator;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Adapter\WorseReflection\Helper\WorseUnresolvableClassNameFinder;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\ReflectorBuilder;

// section: all
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
