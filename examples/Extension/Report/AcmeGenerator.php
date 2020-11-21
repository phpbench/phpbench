<?php
namespace PhpBench\Examples\Extension\Report;

use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
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

    public function generate(SuiteCollection $suiteCollection, Config $config): Document
    {
        $document = new Document();
        $reportsEl = $document->createRoot('reports');
        $reportsEl->setAttribute('name', 'table');
        $reportEl = $reportsEl->appendElement('report');

        $reportEl->setAttribute('title', $config['title']);
        $reportEl->appendElement('description', $config['description']);

        $tableEl = $reportEl->appendElement('table');
        $colsEl = $tableEl->appendElement('cols');

        $col = $colsEl->appendElement('col');
        $col->setAttribute('name', 'candidate');
        $col->setAttribute('label', 'Candidate Cat');
        $col = $colsEl->appendElement('col');
        $col->setAttribute('name', 'is_cat');
        $col->setAttribute('label', 'Is Cat?');

        $tableEl->setAttribute('title', 'This table will explain');

        $groupEl = $tableEl->appendElement('group');
        $groupEl->setAttribute('name', 'body');

        foreach ([
            [ '🐈', 'Yes' ],
            [ '🐕', 'No' ],
        ] as [$symbol, $isCat]) {
            $rowEl = $groupEl->appendElement('row');

            $cellEl = $rowEl->appendElement('cell');
            $cellEl->setAttribute('name', 'symbol');
            $valueEl = $cellEl->appendElement('value', $symbol);
            $cellEl = $rowEl->appendElement('cell');
            $cellEl->setAttribute('name', 'is_cat');
            $valueEl = $cellEl->appendElement('value', $isCat);
        }

        return $document;
    }
}
