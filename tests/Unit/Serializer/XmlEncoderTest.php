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

namespace PhpBench\Tests\Unit\Serializer;

use PhpBench\Environment\Information;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use PhpBench\PhpBench;
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Tests\Util\Approval;

class XmlEncoderTest extends XmlTestCase
{
    protected function setUp(): void
    {
        $this->suiteCollection = $this->prophesize(SuiteCollection::class);
        $this->suite = $this->prophesize(Suite::class);
        $this->env1 = $this->prophesize(Information::class);
        $this->bench1 = $this->prophesize(Benchmark::class);
        $this->subject1 = $this->prophesize(Subject::class);
        $this->variant1 = $this->prophesize(Variant::class);
        $this->iteration1 = $this->prophesize(Iteration::class);
    }

    /**
     * It should encode the suite to an XML document.
     *
     * @dataProvider provideEncode
     */
    public function testEncode(string $path): void
    {
        $approval = Approval::create($path, 2);
        $params = $approval->getConfig(0);

        $collection = $this->getSuiteCollection($params);
        $xmlEncoder = new XmlEncoder();
        $dom = $xmlEncoder->encode($collection);
        $approval->approve(str_replace(PhpBench::version(), 'PHPBENCH_VERSION', $dom->dump()));
    }

    public function doTestBinary(SuiteCollection $collection): void
    {
        $approval = Approval::create(__DIR__ . '/examples/binary1.example', 0);
        $xmlEncoder = new XmlEncoder();
        $dom = $xmlEncoder->encode($collection);
        $approval->approve(str_replace(PhpBench::version(), 'PHPBENCH_VERSION', $dom->dump()));
    }

}
