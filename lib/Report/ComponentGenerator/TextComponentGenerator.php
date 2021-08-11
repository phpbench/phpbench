<?php

namespace PhpBench\Report\ComponentGenerator;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\Text;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextComponentGenerator implements ComponentGeneratorInterface
{
    public const PARAM_TEXT = 'text';

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    public function __construct(ExpressionEvaluator $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setRequired(self::PARAM_TEXT);
        $options->setAllowedTypes(self::PARAM_TEXT, ['string']);
    }

    /**
     * {@inheritDoc}
     */
    public function generateComponent(DataFrame $dataFrame, array $config): ComponentInterface
    {
        return new Text($this->evaluator->renderTemplate($config[self::PARAM_TEXT], [
            'frame' => $dataFrame
        ]));
    }
}
