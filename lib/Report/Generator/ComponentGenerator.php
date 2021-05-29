<?php

namespace PhpBench\Report\Generator;

use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Report\ComponentGeneratorAgent;
use PhpBench\Report\Component\ReportComponent;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Builder\ReportBuilder;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComponentGenerator implements GeneratorInterface
{
    const PARAM_COMPONENTS = 'components';
    const KEY_COMPONENT_TYPE = '_type';

    /**
     * @var ComponentGeneratorAgent
     */
    private $agent;

    /**
     * @var SuiteCollectionTransformer
     */
    private $transformer;

    public function __construct(
        ComponentGeneratorAgent $agent,
        SuiteCollectionTransformer $transformer
    )
    {
        $this->agent = $agent;
        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::PARAM_COMPONENTS => [],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteCollection $collection, Config $config): Reports
    {
        $dataFrame = $this->transformer->suiteToFrame($collection);
        $reports = [];

        foreach ($config[self::PARAM_COMPONENTS] as $component) {
            if (!isset($component[self::KEY_COMPONENT_TYPE])) {
                throw new RuntimeException(
                    'Component definition must have `_type` key indicating the component type'
                );
            }
            $componentGenerator = $this->agent->get($component[self::KEY_COMPONENT_TYPE]);
            unset($component[self::KEY_COMPONENT_TYPE]);
            $report = $componentGenerator->generateComponent(
                $dataFrame,
                $this->agent->resolveConfig($componentGenerator, $component)
            );
            if (!$report instanceof Report) {
                $report = ReportBuilder::create()->addObject($report)->build();
            }
            $reports[] = $report;
        }

        return Reports::fromReports(...$reports);
    }
}
