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

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\Registry\Config;
use PhpBench\Report\Generator\EnvGenerator;
use PhpBench\Report\Generator\TableGenerator;
use PhpBench\Tests\Util\TestUtil;

class EnvGeneratorTest extends GeneratorTestCase
{
    /**
     * @var TableGenerator
     */
    private $generator;

    protected function setUp(): void
    {
        $this->generator = new EnvGenerator();
    }

    /**
     * It should generate an environment report.
     */
    public function testEnv()
    {
        $collection = TestUtil::createCollection([
            [
                'env' => [
                    'vcs' => [
                        'branch' => 'my_branch',
                        'version' => 'a1b2c3d4',
                    ],
                    'uname' => [
                        'os' => 'Linux',
                        'version' => '4.2.0',
                    ],
                ],
            ],
        ]);

        $report = $this->generator->generate($collection, new Config('foo', []));
        $this->assertXPathCount($report, 3, '//col');
        $this->assertXPathCount($report, 1, '//table[contains(@title, "Suite #0")]');
        $this->assertXPathCount($report, 4, '//row');
        $this->assertXPathCount($report, 12, '//cell');
        $this->assertXPathCount($report, 2, '//row[cell[@name="provider"]/value = "vcs"]');
        $this->assertXPathCount($report, 1, '//row[cell[@name="key"] = "branch"]');
        $this->assertXPathCount($report, 1, '//row[cell[@name="value"] = "my_branch"]');
    }

    /**
     * It should generate an environment report for each suite.
     */
    public function testEnvEachSuite()
    {
        $collection = TestUtil::createCollection([
            [
                'env' => [
                    'vcs' => [
                        'branch' => 'my_branch',
                    ],
                ],
            ],
            [
                'env' => [
                    'vcs' => [
                        'branch' => 'my_branch',
                    ],
                ],
            ],
        ]);

        $report = $this->generator->generate($collection, new Config('foo', []));
        $this->assertXPathCount($report, 1, '//table[contains(@title, "Suite #0")]');
        $this->assertXPathCount($report, 1, '//table[contains(@title, "Suite #1")]');
    }
}
