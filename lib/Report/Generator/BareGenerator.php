<?php

namespace PhpBench\Report\Generator;

use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BareGenerator implements GeneratorInterface
{
    /**
     * @var SuiteCollectionTransformer
     */
    private $transformer;

    public function __construct(SuiteCollectionTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteCollection $collection, Config $config): Reports
    {
        return Reports::fromReport(
            Report::fromTable(
                Table::fromRowArray(
                    array_map(function (array $row) {
                        return array_map(function ($value) {
                            return PhpValueFactory::fromValue($value);
                        }, $row);
                    }, $this->transformer->suiteToTable($collection))
                )
            )
        );
    }
}
