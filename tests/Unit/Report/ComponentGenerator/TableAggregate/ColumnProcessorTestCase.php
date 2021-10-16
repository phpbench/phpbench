<?php

namespace PhpBench\Tests\Unit\Report\ComponentGenerator\TableAggregate;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Report\ComponentGenerator\TableAggregate\ColumnProcessorInterface;
use PhpBench\Tests\IntegrationTestCase;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class ColumnProcessorTestCase extends IntegrationTestCase
{
    abstract public function createProcessor(): ColumnProcessorInterface;

    /**
     * @param tableColumnDefinition $definition
     * @param parameters $params
     *
     * @return array<string, mixed>
     */
    public function processRow(array $definition, DataFrame $frame, array $params): array
    {
        $resolver = new OptionsResolver();
        $processor = $this->createProcessor();
        $processor->configure($resolver);

        return array_map(function (Node $node) {
            if (!$node instanceof PhpValue) {
                throw new RuntimeException(sprintf('Value did not resolve to a php value, got "%s"', get_class($node)));
            }

            return $node->value();
        }, $processor->process([], $resolver->resolve($definition), $frame, $params));
    }
}
