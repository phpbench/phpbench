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

namespace PhpBench\Benchmark\Metadata;

use PhpBench\Model\Benchmark;
use PhpBench\Model\Subject;

/**
 * Benchmark metadata class.
 */
class BenchmarkMetadata
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $class;

    /**
     * @var array<string, SubjectMetadata> indexed by subject name
     */
    private $subjects = [];

    /**
     * @var string[]
     */
    private $beforeClassMethods = [];

    /**
     * @var string[]
     */
    private $afterClassMethods = [];

    /**
     * @var ExecutorMetadata|null
     */
    private $executorMetadata;

    /**
     */
    public function __construct(string $path, string $class)
    {
        $this->path = $path;
        $this->class = $class;
    }

    /**
     * Get the file path of this benchmark.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get or create a new SubjectMetadata instance with the given name.
     *
     */
    public function getOrCreateSubject(string $name): SubjectMetadata
    {
        if (isset($this->subjects[$name])) {
            return $this->subjects[$name];
        }

        $this->subjects[$name] = new SubjectMetadata($this, $name);

        return $this->subjects[$name];
    }

    /**
     * Get the subject metadata instances for this benchmark metadata.
     *
     * @return array<string, SubjectMetadata> indexed by subject name
     */
    public function getSubjects(): array
    {
        return $this->subjects;
    }

    /**
     * Remove all subjects whose name is not in the given list.
     *
     * @param string[] $filters
     */
    public function filterSubjectNames(array $filters): void
    {
        foreach (array_keys($this->subjects) as $subjectName) {
            if (false === Subject::matchesPatterns($this->class, $subjectName, $filters)) {
                unset($this->subjects[$subjectName]);
            }
        }
    }

    /**
     * Remove all the subjects which are not contained in the given list of groups.
     *
     * @param string[] $groups
     */
    public function filterSubjectGroups(array $groups): void
    {
        foreach ($this->subjects as $subjectName => $subject) {
            if (0 === count(array_intersect($subject->getGroups(), $groups))) {
                unset($this->subjects[$subjectName]);
            }
        }
    }

    /**
     * Return true if there are subjects in this benchmark metadata, false if not.
     */
    public function hasSubjects(): bool
    {
        return 0 !== count($this->subjects);
    }

    /**
     * Return the benchmark class.
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Return any methods that should be called before the benchmark class is executed.
     */
    public function getBeforeClassMethods(): array
    {
        return $this->beforeClassMethods;
    }

    /**
     * Set any methods that should be called before the benchmark class is executed.
     *
     */
    public function setBeforeClassMethods(array $beforeClassMethods): void
    {
        $this->beforeClassMethods = $beforeClassMethods;
    }

    /**
     * Return any methods that should be called after the benchmark class is executed.
     */
    public function getAfterClassMethods(): array
    {
        return $this->afterClassMethods;
    }

    /**
     * Set any methods that should be called after the benchmark class is executed.
     *
     */
    public function setAfterClassMethods(array $afterClassMethods): void
    {
        $this->afterClassMethods = $afterClassMethods;
    }

    public function getIterator(): array
    {
        return $this->subjects;
    }

    public function getExecutor(): ?ExecutorMetadata
    {
        return $this->executorMetadata;
    }

    public function setExecutor(ExecutorMetadata $serviceMetadata): void
    {
        $this->executorMetadata = $serviceMetadata;
    }

    public function merge(self $benchmarkMetadata): void
    {
        $this->beforeClassMethods = array_merge($this->beforeClassMethods, $benchmarkMetadata->beforeClassMethods);
        $this->afterClassMethods = array_merge($this->afterClassMethods, $benchmarkMetadata->afterClassMethods);

        foreach ($benchmarkMetadata->getSubjects() as $subject) {
            $this->getOrCreateSubject($subject->getName())->merge($subject);
        }
    }
}
