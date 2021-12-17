<?php

namespace PhpBench\Executor\Parser;

use PhpBench\Executor\Parser\Ast\UnitNode;
use RuntimeException;

class UnitParser
{
    public function parse(array $program, ?UnitNode $node = null): UnitNode
    {
        $node = $node ?: new UnitNode('root');
        $currentNode = $node;

        foreach ($program as $nameOrChildren) {
            if (is_array($nameOrChildren)) {
                $this->parse($nameOrChildren, $currentNode);

                continue;
            }

            if (is_string($nameOrChildren)) {
                $currentNode = new UnitNode($nameOrChildren);
                $node->children[] = $currentNode;

                continue;
            }

            throw new RuntimeException(sprintf(
                'Invalid unit type when parsing program: "%s"',
                gettype($nameOrChildren)
            ));
        }

        return $node;
    }
}
