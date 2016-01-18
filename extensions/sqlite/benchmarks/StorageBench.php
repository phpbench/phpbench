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
        $this->setContainerConfig(array(
            'storage' => 'sqlite',
            'storage.sqlite.db_path' => $this->getWorkspacePath() . '/test.sqlite',
        ));
    }

    /**
     * @ParamProviders({"provideIterations"})
     */
    public function benchStore($params)
    {
        $this->runCommand('console.command.run', array(
            'path' => $this->getFunctionalBenchmarkPath(),
            '--store' => true,
            '--iterations' => $params['nb_iterations'],
        ));
    }

    public function provideIterations()
    {
        return array(
            array('nb_iterations' => 1),
            array('nb_iterations' => 10),
            array('nb_iterations' => 100),
        );
    }
}
