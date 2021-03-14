<?php

namespace PhpBench\Tests\Unit\Report\Generator;

use PHPUnit\Framework\TestCase;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\ExpressionGenerator;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Tests\Util\VariantBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionGeneratorTest extends IntegrationTestCase
{
    public function testGenerate(): void
    {
        $suite = TestUtil::createSuite([
            'subjects' => [ 'one', 'two' ],
            'iterations' => [ 10, 20, 10, 30 ],
        ]);
        $container = $this->container();
        $generator = new ExpressionGenerator(
            $container->get(ExpressionLanguage::class),
            $container->get(Evaluator::class),
            $container->get(Printer::class)
        );
        $options = new OptionsResolver();
        $generator->configure($options);

        ($generator)->generate(
            new SuiteCollection([$suite]),
            new Config('asd', $options->resolve([]))
        );
    }
}
