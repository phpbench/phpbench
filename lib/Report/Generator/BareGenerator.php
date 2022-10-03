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

use function array_map;

class BareGenerator implements GeneratorInterface
{
    public const PARAM_VERTICAL = 'vertical';

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
        $options->setDefaults([
            self::PARAM_VERTICAL => false,
        ]);
        $options->setAllowedTypes(self::PARAM_VERTICAL, ['bool']);
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteCollection $collection, Config $config): Reports
    {
        if ($config[self::PARAM_VERTICAL]) {
            return Reports::fromReport(
                Report::fromTables(array_map(function (array $table) {
                    return Table::fromRowArray(array_map(function ($key, $value) {
                        return [
                            'field' => PhpValueFactory::fromValue($key),
                            'value' => PhpValueFactory::fromValue($value),
                        ];
                    }, array_keys($table), array_values($table)));
                }, $this->transformer->suiteToFrame($collection)->toRecords()))
            );
        }

        return Reports::fromReport(
            Report::fromTable(
                Table::fromRowArray(
                    array_map(function (array $row) {
                        return array_map(function ($value) {
                            return PhpValueFactory::fromValue($value);
                        }, $row);
                    }, $this->transformer->suiteToFrame($collection)->toRecords())
                )
            )
        );
    }
}
