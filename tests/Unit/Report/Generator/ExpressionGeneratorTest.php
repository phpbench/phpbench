<?php

namespace PhpBench\Tests\Unit\Report\Generator;

use PHPUnit\Framework\TestCase;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\ExpressionGenerator;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Tests\Util\VariantBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $suite = TestUtil::createSuite([
            'subjects' => [ 'one', 'two' ],
            'iterations' => [ 10, 20, 10, 30 ],
        ]);
        $generator = new ExpressionGenerator();
        $options = new OptionsResolver();
        $generator->configure($options);

        ($generator)->generate(
            new SuiteCollection([$suite]),
            new Config('asd', $options->resolve([]))
        );
    }
}
