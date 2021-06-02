<?php

namespace PhpBench\Report\Generator;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Data\DataFrame;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\ComponentGeneratorAgent;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Builder\ReportBuilder;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComponentGenerator implements ComponentGeneratorInterface, GeneratorInterface
{
    private const PARAM_TITLE = 'title';
    private const PARAM_DESCRIPTION = 'description';
    private const PARAM_PARTITION = 'partition';
    private const PARAM_COMPONENTS = 'components';
    private const KEY_COMPONENT_TYPE = '_type';

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
            self::PARAM_COMPONENTS => [],
        ]);
        $options->setAllowedTypes(self::PARAM_TITLE, ['string', 'null']);
        $options->setAllowedTypes(self::PARAM_DESCRIPTION, ['string', 'null']);
        $options->setAllowedTypes(self::PARAM_PARTITION, ['array']);
        $options->setAllowedTypes(self::PARAM_COMPONENTS, ['array']);
        SymfonyOptionsResolverCompat::setInfos($options, [
            self::PARAM_TITLE => 'Title for generated report',
            self::PARAM_DESCRIPTION => 'Description for generated report',
            self::PARAM_PARTITION => 'Partition the data using these column names - the row expressions will to aggregate the data in each partition',
            self::PARAM_COMPONENTS => 'List of component configuration objects, each component must feature a `_type` key (e.g. `table_aggregate`)',
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
        $builder = ReportBuilder::create(
            $config[self::PARAM_TITLE] ? $this->evaluator->renderTemplate(
                $config[self::PARAM_TITLE],
                ['frame' => $dataFrame]
            ) : null
        );

        if ($config[self::PARAM_DESCRIPTION]) {
            $builder->withDescription($this->evaluator->renderTemplate($config[self::PARAM_DESCRIPTION], [
                'frame' => $dataFrame
            ]));
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
}
