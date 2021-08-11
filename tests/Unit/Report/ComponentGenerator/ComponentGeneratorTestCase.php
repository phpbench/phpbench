<?php

namespace PhpBench\Tests\Unit\Report\ComponentGenerator;

use PhpBench\Data\DataFrame;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Tests\IntegrationTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class ComponentGeneratorTestCase extends IntegrationTestCase
{
    abstract public function createGenerator(): ComponentGeneratorInterface;

    /**
     * @param parameters $config
     */
    public function generate(DataFrame $dataFrame, array $config): ComponentInterface
    {
        $resolver = new OptionsResolver();
        $generator = $this->createGenerator();
        $generator->configure($resolver);

        return $generator->generateComponent($dataFrame, $resolver->resolve($config));
    }
}
