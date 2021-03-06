<?php

namespace PhpBench\Report\Generator;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Data\DataFrame;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Builder\ReportBuilder;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComponentGenerator implements ComponentGeneratorInterface, GeneratorInterface
{
    const PARAM_TITLE = 'title';
    const PARAM_DESCRIPTION = 'description';
    const PARAM_PARTITION = 'partition';
    const PARAM_COMPONENTS = 'components';
    const PARAM_TABBED = 'tabbed';
    const PARAM_TAB_LABELS = 'tab_labels';
    const KEY_COMPONENT_TYPE = 'component';

    /**
     * @var ConfigurableRegistry
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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        SuiteCollectionTransformer $transformer,
        ConfigurableRegistry $agent,
        ExpressionEvaluator $evaluator,
        LoggerInterface $logger
    ) {
        $this->agent = $agent;
        $this->evaluator = $evaluator;
        $this->transformer = $transformer;
        $this->logger = $logger;
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
            self::PARAM_TABBED => false,
            self::PARAM_TAB_LABELS => [],
        ]);
        $options->setAllowedTypes(self::PARAM_TITLE, ['string', 'null']);
        $options->setAllowedTypes(self::PARAM_DESCRIPTION, ['string', 'null']);
        $options->setAllowedTypes(self::PARAM_PARTITION, ['string[]']);
        $options->setAllowedTypes(self::PARAM_COMPONENTS, ['array[]']);
        $options->setAllowedTypes(self::PARAM_TABBED, ['bool']);
        $options->setAllowedTypes(self::PARAM_TAB_LABELS, ['string[]']);
        SymfonyOptionsResolverCompat::setInfos($options, [
            self::PARAM_TITLE => 'Title for generated report',
            self::PARAM_DESCRIPTION => 'Description for generated report',
            self::PARAM_PARTITION => 'Partition the data using these column names - components will be rendered for each partition',
            self::PARAM_COMPONENTS => 'List of component configuration objects, each component must feature a ``component`` key (e.g. ``table_aggregate``)',
            self::PARAM_TABBED => 'Render components in tabs when supported in the output renderer (e.g. HTML)',
            self::PARAM_TAB_LABELS => 'List of labels for tabs, will replace the default labels from left to right.',
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
                        'Component definition must have `component` key indicating the component type'
                    );
                }
                $componentGenerator = $this->agent->getService($component[self::KEY_COMPONENT_TYPE]);
                assert($componentGenerator instanceof ComponentGeneratorInterface);
                $builder->addObject($this->doGenerateComponent(
                    $componentGenerator,
                    $parition,
                    $this->agent->getConfig($component)->getArrayCopy()
                ));
            }
        }

        if ($config[self::PARAM_TABBED]) {
            $builder->enableTabs();
        }

        $builder->withTabLabels($config[self::PARAM_TAB_LABELS]);

        return $builder->build();
    }

    private function doGenerateComponent(
        ComponentGeneratorInterface $componentGenerator,
        DataFrame $parition,
        array $config
    ): ComponentInterface {
        $start = microtime(true);
        $component = $componentGenerator->generateComponent(
            $parition,
            $config
        );
        $this->logger->debug(sprintf('Rendered component "%s" (%s) for "%s" in "%ss"',
            get_class($component),
            $component->title(),
            get_class($componentGenerator),
            number_format(microtime(true) - $start, 2)
        ));

        return $component;
    }
}
