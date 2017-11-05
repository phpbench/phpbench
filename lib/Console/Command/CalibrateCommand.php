<?php

namespace PhpBench\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Storage\DriverInterface;
use Symfony\Component\Console\Input\InputOption;
use PhpBench\Registry\Registry;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Benchmark\Runner;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Variant;
use PhpBench\Model\Subject;
use PhpBench\Benchmark\RunnerContext;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Result\ComputedResult;

class CalibrateCommand extends Command
{
    /**
     * @var DriverInterface
     */
    private $storage;

    /**
     * @var Runner
     */
    private $runner;

    public function __construct(
        Registry $storage,
        Runner $runner
    )
    {
        parent::__construct();
        $this->storage = $storage;
        $this->runner = $runner;
    }

    protected function configure()
    {
        $this->setName('calibrate');
        $this->addOption('uuid', null, InputOption::VALUE_REQUIRED, 'UUID of run to calibrate against', 'latest');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $suites = $this->suiteCollection($input)->getSuites();

        foreach ($suites as $suite) {
            foreach ($suite->getBenchmarks() as $benchmark) {
                foreach ($benchmark->getSubjects() as $subject) {
                    foreach ($subject->getVariants() as $variant) {
                        $context = $this->contextFrom($suite, $benchmark, $subject, $variant);
                        $actualSuite = $this->runner->run($context);
                        $this->outputDifference($output, $variant, $actualSuite);
                    }
                }
            }
        }
    }

    private function suiteCollection(InputInterface $input): SuiteCollection
    {
        return $this->storage->getService()->fetch($input->getOption('uuid'));
    }

    private function contextFrom(Suite $suite, Benchmark $benchmark, Subject $subject, Variant $variant)
    {
        $context = new RunnerContext(getcwd() . '/benchmarks', [
            'filters' => [ sprintf('%s::%s', $benchmark->getClass(), $subject->getName()) ],
        ]);

        return $context;
    }

    private function outputDifference(OutputInterface $output, Variant $referenceVariant, Suite $newSuite)
    {
        $reference = $referenceVariant->getStats()->getMean();
        $actual = $newSuite->getSummary()->getMeanTime();
        $scaler = $reference / $actual;

        $output->writeln(sprintf(
            "%s::%s\t <comment>Reference: </>%s<comment> Actual: </>%s <comment>Scaler: </>%s",
            $referenceVariant->getSubject()->getBenchmark()->getClass(), $referenceVariant->getSubject()->getName(),
            $reference, $actual, $scaler
        ));
    }
}
