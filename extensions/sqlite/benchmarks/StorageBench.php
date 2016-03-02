<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\Sqlite\Benchmarks;

use PhpBench\Benchmarks\Macro\BaseBenchCase;

class StorageBench extends BaseBenchCase
{
    public function __construct()
    {
        $this->addContainerExtensionClass('PhpBench\\Extensions\\Sqlite\\SqliteExtension');
        $this->setContainerConfig([
            'storage' => 'sqlite',
            'storage.sqlite.db_path' => $this->getWorkspacePath() . '/test.sqlite',
        ]);
    }

    /**
     * @ParamProviders({"provideIterations"})
     */
    public function benchStore($params)
    {
        $this->runCommand('console.command.run', [
            'path' => $this->getFunctionalBenchmarkPath(),
            '--store' => true,
            '--iterations' => $params['nb_iterations'],
        ]);
    }

    public function provideIterations()
    {
        return [
            ['nb_iterations' => 1],
            ['nb_iterations' => 10],
            ['nb_iterations' => 100],
        ];
    }
}
