<?php
namespace PhpBench\Examples\Extension\Report;

use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AcmeGenerator implements GeneratorInterface
{
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'title' => 'Cats report',
            'description' => 'Are cats really cats or are they dogs?',
        ]);
    }

    public function generate(SuiteCollection $suiteCollection, Config $config): Reports
    {
        $rows = [];

        foreach ([
            [ '🐈', 'Yes' ],
            [ '🐕', 'No' ],
        ] as [$symbol, $isCat]) {
            $rows[] = [
                'symbol' => $symbol,
                'is_cat' => $isCat,
            ];
        }

        return Reports::fromReport(
            new Report(
                [
                    Table::fromRowArray($rows, 'Cat or dog?'),
                ],
                $config['title'],
                $config['description']
            )
        );
    }
}
