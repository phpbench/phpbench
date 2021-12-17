<?php

namespace PhpBench\Tests\Unit\Executor;

use PHPUnit\Framework\TestCase;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\Parser\UnitParser;
use PhpBench\Executor\ScriptBuilder;
use PhpBench\Executor\Unit\TestUnit;

class ScriptBuilderTest extends TestCase
{
    public function testBuildScriptWithNesting(): void
    {
        self::assertEquals(<<<EOT
<?php
// >>> root

  // >>> stage1
  {{
    // >>> stage2
    <<
    >>
    // <<< stage2
    // >>> stage3
    <<
    >>
    // <<< stage3
  }}
  // <<< stage1

// <<< root
EOT
        , $this->build(
            ['stage1', ['stage2', 'stage3']],
            new TestUnit('root', '', ''),
            new TestUnit('stage1', '{{', '}}'),
            new TestUnit('stage2', '<<', '>>'),
            new TestUnit('stage3', '<<', '>>'),
        ));
    }

    private function build(array $program, TestUnit ...$testStages): string
    {
        $node = (new UnitParser())->parse($program);
        $context = new ExecutionContext('foo', 'path', 'method');

        return (new ScriptBuilder($testStages))->build($context, $node);
    }
}
