<?php

namespace PhpBench\Tests\Unit\Executor;

use PHPUnit\Framework\TestCase;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\Parser\StageLexer;
use PhpBench\Executor\Parser\StageParser;
use PhpBench\Executor\ScriptBuilder;
use PhpBench\Executor\Stage\TestStage;

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
            'stage1{stage2;stage3}',
            new TestStage('root', '', ''),
            new TestStage('stage1', '{{', '}}'),
            new TestStage('stage2', '<<', '>>'),
            new TestStage('stage3', '<<', '>>'),
        ));
    }

    private function build(string $program, TestStage ...$testStages): string
    {
        $node = (new StageParser())->parse((new StageLexer())->lex($program));
        $context = new ExecutionContext('foo', 'path', 'method');
        return (new ScriptBuilder($testStages))->build($context, $node);
    }
}
