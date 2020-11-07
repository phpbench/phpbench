<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Executor\HealthCheckInterface;
use PhpBench\Executor\MethodExecutorInterface;
use PhpBench\Model\Iteration;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestExecutor implements BenchmarkExecutorInterface, MethodExecutorInterface, HealthCheckInterface
{
    /**
     * @var array
     */
    private $executedMethods = [];

    /**
     * @var bool
     */
    private $healthChecked = false;

    /**
     * @var array<SubjectMetadata>
     */
    private $executedSubjects = [];

    /**
     * @var SubjectMetadata|null
     */
    private $lastSubject;

    /**
     * @var Variant|null
     */
    private $lastVariant;

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'results' => [],
            'exception' => null,
        ]);
    }

    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config): ExecutionResults
    {
        if ($config['exception']) {
            throw $config['exception'];
        }
        $this->executedSubjects[] = $subjectMetadata;
        $this->lastSubject = $subjectMetadata;
        $this->lastVariant = $iteration->getVariant();

        return ExecutionResults::fromResults(...$config['results']);
    }

    public function executeMethods(BenchmarkMetadata $benchmark, array $methods): void
    {
        $this->executedMethods = array_merge($this->executedMethods, $methods);
    }

    /**
     * {@inheritDoc}
     */
    public function healthCheck(): void
    {
        $this->healthChecked = true;
    }

    public function lastSubjectOrException(): SubjectMetadata
    {
        if (null === $this->lastSubject) {
            throw new RuntimeException(
                'No subject has been executed'
            );
        }

        return $this->lastSubject;
    }

    public function lastVariantOrException(): Variant
    {
        if (null === $this->lastVariant) {
            throw new RuntimeException(
                'No variant has been executed'
            );
        }

        return $this->lastVariant;
    }

    public function hasMethodBeenExecuted(string $name): bool
    {
        return in_array($name, $this->executedMethods);
    }

    public function hasHealthBeenChecked(): bool
    {
        return $this->healthChecked;
    }

    public function getExecutedSubjectCount(): int
    {
        return count($this->executedSubjects);
    }
}
