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

namespace PhpBench\Benchmark\Executor;

use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Benchmark\ExecutorInterface;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;

class PingExecutor implements ExecutorInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config)
    {
        $ch = curl_init('http://www.example.com/');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY  , true);  // we don't need body
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $start = microtime(true);
        curl_exec($ch);
        $end = microtime(true);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $iteration->setResult(new TimeResult((int) (($end * 1E6) - ($start * 1E6))));
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults([
        ]);
    }

    /**
     * Execute arbitrary methods.
     *
     * This should be called based on the value of `@BeforeClassMethods` and `@AfterClassMethods`
     * and used to establish some persistent state.
     *
     * Methods called here cannot establish a runtime state.
     *
     * @param string[]
     */
    public function executeMethods(BenchmarkMetadata $benchmark, array $methods)
    {
    }
}

