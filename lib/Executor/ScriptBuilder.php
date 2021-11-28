<?php

namespace PhpBench\Executor;

use PhpBench\Executor\Parser\Ast\StageNode;
use RuntimeException;
use function str_repeat;

class ScriptBuilder
{
    /**
     * @var array<string, UnitInterface>
     */
    private $scriptStages;

    /**
     * @param UnitInterface[] $scriptStages
     */
    public function __construct(array $scriptStages)
    {
        $this->scriptStages = array_combine(array_map(static function (UnitInterface $stage): string {
            return $stage->name();
        }, $scriptStages), array_values($scriptStages));
    }

    public function build(ExecutionContext $context, StageNode $node): string
    {
        return implode("\n", array_merge(['<?php'], $this->renderNode($context, $node)));
    }

    /**
     * @return string[]
     */
    private function renderNode(ExecutionContext $context, StageNode $node, int $indentation = 0): array
    {
        if (!isset($this->scriptStages[$node->name])) {
            throw new RuntimeException(sprintf(
                'Unknown stage "%s", known stages: "%s"',
                $node->name, implode('", "', array_keys($this->scriptStages))
            ));
        }

        $stage = $this->scriptStages[$node->name];
        $lines = $this->indent(array_merge([
            sprintf('// >>> %s', $node->name),
        ], $stage->start($context)), $indentation);
        foreach ($node->children as $child) {
            $lines = array_merge(
                $lines,
                $this->renderNode($context, $child, $indentation + 1)
            );
        }
        $lines = array_merge(
            $lines,
            $this->indent(array_merge($stage->end($context), [
                sprintf('// <<< %s', $node->name)
            ]), $indentation),
        );

        return $lines;
    }

    /**
     * @param string[] $lines
     * @return string[] 
     */
    private function indent(array $lines, int $indentation): array
    {
        return array_map(function (string $line) use ($indentation) {
            return sprintf('%s%s', str_repeat('  ', $indentation), $line);
        }, $lines);
    }
}
