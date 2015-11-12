<?php

namespace PhpBench\Benchmark;

use Symfony\Component\Process\Process;
use PhpBench\Benchmark\SuiteDocument;

class ConcurrentRunner extends Runner
{
    public function runAll($path)
    {
        if (1 == $this->concurrency) {
            return parent::runAll($path);
        }

        $aggregateDocument = null;

        $processes = array();
        for ($procIndex = 0; $procIndex < $this->concurrency; $procIndex++) {
            $cmd = $_SERVER['SCRIPT_FILENAME'] . ' run --concurrency=1 --dump-file=proc' . $procIndex . '.xml ' . $path;
            $process = new Process($cmd);
            $process->start();
            $processes[] = $process;
        }

        foreach ($processes as $procIndex => $process) {
            $process->wait(function ($type, $buffer) {
                if ($type === Process::ERR) {
                    echo 'ERR> ' . $buffer;
                    return;
                }
                echo $buffer;
            });

            $suite = new SuiteDocument();
            $suite->load('proc' . $procIndex . '.xml');

            $iterationEls = $suite->xpath()->query('//iteration');
            foreach ($iterationEls as $iterationEl) {
                $iterationEl->setAttribute('process', $procIndex);
            }

            if (null === $aggregateDocument) {
                $aggregateDocument = $suite;
                continue;
            }

            foreach ($iterationEls as $iterationEl) {
                $node = $aggregateDocument->importNode($iterationEl);
                $path = $iterationEl->getNodePath();
                preg_match('{(.*)/iteration.*$}', $path, $matches);
                $parentNodeList = $aggregateDocument->xpath()->query($matches[1]);
                if ($parentNodeList->length !== 1) {
                    throw new \RuntimeException('Could not find parent node');
                }
                $parentNode = $parentNodeList->item(0);
                $parentNode->appendChild($node);
            }
        }

        return $aggregateDocument;
    }
}
