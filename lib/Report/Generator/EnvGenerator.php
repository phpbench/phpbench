<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Report\Generator;

use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Report generator for environmental information.
 *
 * NOTE: The Table report generator could probably be improved to be able to incorporate
 *       this report somehow.
 */
class EnvGenerator implements GeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'title' => null,
            'description' => null,
        ]);
        $options->setAllowedTypes('title', ['null', 'scalar']);
        $options->setAllowedTypes('description', ['null', 'scalar']);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SuiteCollection $suiteCollection, Config $config): Reports
    {
        $tables = [];

        foreach ($suiteCollection as $suite) {
            $title = sprintf(
                'Suite #%s %s',
                $suite->getUuid(),
                $suite->getDate()->format('Y-m-d H:i:s')
            );

            $rows = [];

            foreach ($suite->getEnvInformations() as $envInformation) {
                foreach ($envInformation as $key => $value) {
                    $rows[] = [
                        'provider' => new StringNode($envInformation->getName()),
                        'key' => new StringNode($key),
                        'value' => PhpValueFactory::fromValue($value)
                    ];
                }
            }
            $tables[] = Table::fromRowArray($rows, $title);
        }

        return Reports::fromReport(
            Report::fromTables(
                $tables,
                isset($config['title']) ? $config['title'] : null,
                isset($config['description']) ? $config['description'] : null
            )
        );
    }
}
