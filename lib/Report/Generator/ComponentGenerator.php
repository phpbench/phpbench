<?php

namespace PhpBench\Report\Generator;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Registry\Registry;
use PhpBench\Report\ComponentGeneratorAgent;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Builder\ReportBuilder;
use PhpBench\Report\Model\Builder\TableBuilder;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComponentGenerator implements ComponentGeneratorInterface, GeneratorInterface
{
    const PARAM_TITLE = 'title';
    const PARAM_DESCRIPTION = 'description';
    const PARAM_PARTITION = 'partition';
    const PARAM_COMPONENTS = 'components';
    const KEY_COMPONENT_TYPE = '_type';

    /**
     * @var ComponentGeneratorAgent
     */
    private $agent;

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    /**
     * @var SuiteCollectionTransformer
     */
    private $transformer;

    public function __construct(SuiteCollectionTransformer $transformer, ComponentGeneratorAgent $agent, ExpressionEvaluator $evaluator)
    {
        $this->agent = $agent;
        $this->evaluator = $evaluator;
        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::PARAM_TITLE => null,
            self::PARAM_DESCRIPTION => null,
            self::PARAM_PARTITION => [],
            self::PARAM_COMPONENTS => null,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteCollection $collection, Config $config): Reports
    {
        $dataFrame = $this->transformer->suiteToFrame($collection);
        $component = $this->generateComponent($dataFrame, $config->getArrayCopy());
        assert($component instanceof Report);
        return Reports::fromReport($component);
    }

    /**
     * {@inheritDoc}
     */
    public function generateComponent(DataFrame $dataFrame, array $config): ComponentInterface
    {
        $builder = ReportBuilder::create($this->resolveTitle($dataFrame, $config));
        if ($config[self::PARAM_DESCRIPTION]) {
            $builder->withDescription($config[self::PARAM_DESCRIPTION]);
        }

        foreach ($dataFrame->partition($config[self::PARAM_PARTITION]) as $parition) {
            foreach ($config[self::PARAM_COMPONENTS] as $component) {
                if (!isset($component[self::KEY_COMPONENT_TYPE])) {
                    throw new RuntimeException(
                        'Component definition must have `_type` key indicating the component type'
                    );
                }
                $componentGenerator = $this->agent->get($component[self::KEY_COMPONENT_TYPE]);
                unset($component[self::KEY_COMPONENT_TYPE]);
                $builder->addObject($componentGenerator->generateComponent(
                    $parition,
                    $this->agent->resolveConfig($componentGenerator, $component)
                ));
            }
        }

        return $builder->build();
    }

    /**
     * @param parameters $config
     */
    private function resolveTitle(DataFrame $frame, array $config): ?string
    {
        if (!isset($config[self::PARAM_TITLE])) {
            return null;
        }
        $title = $this->evaluator->evaluate($config[self::PARAM_TITLE], [
            'frame' => $frame
        ]);
        assert($title instanceof PhpValue);
        return $title->value();
    }
}
