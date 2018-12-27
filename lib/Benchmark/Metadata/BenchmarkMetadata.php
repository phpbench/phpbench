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
     * @var SubjectMetadata[]
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
     * @var ExecutorMetadata
     */
    private $executorMetadata;

    /**
     * @param string $path
     * @param string $class
     */
    public function __construct($path, $class)
    {
        $this->path = $path;
        $this->class = $class;
    }

    /**
     * Get the file path of this benchmark.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get or create a new SubjectMetadata instance with the given name.
     *
     * @param string $name
     *
     * @return SubjectMetadata
     */
    public function getOrCreateSubject($name)
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
     * @return SubjectMetadata[]
     */
    public function getSubjects()
    {
        return $this->subjects;
    }

    /**
     * Remove all subjects whose name is not in the given list.
     *
     * @param string[] $filters
     */
    public function filterSubjectNames(array $filters)
    {
        foreach (array_keys($this->subjects) as $subjectName) {
            $unset = true;

            foreach ($filters as $filter) {
                if (preg_match(
                    sprintf('{^.*?%s.*?$}', $filter),
                    sprintf('%s::%s', $this->getClass(), $subjectName)
                )) {
                    $unset = false;

                    break;
                }
            }

            if (true === $unset) {
                unset($this->subjects[$subjectName]);
            }
        }
    }

    /**
     * Remove all the subjects which are not contained in the given list of groups.
     *
     * @param string[] $groups
     */
    public function filterSubjectGroups(array $groups)
    {
        foreach ($this->subjects as $subjectName => $subject) {
            if (0 === count(array_intersect($subject->getGroups(), $groups))) {
                unset($this->subjects[$subjectName]);
            }
        }
    }

    /**
     * Return true if there are subjects in this benchmark metadata, false if not.
     *
     * @return bool
     */
    public function hasSubjects()
    {
        return 0 !== count($this->subjects);
    }

    /**
     * Return the benchmark class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Return any methods that should be called before the benchmark class is executed.
     */
    public function getBeforeClassMethods()
    {
        return $this->beforeClassMethods;
    }

    /**
     * Set any methods that should be called before the benchmark class is executed.
     *
     * @param array $beforeClassMethods
     */
    public function setBeforeClassMethods(array $beforeClassMethods)
    {
        $this->beforeClassMethods = $beforeClassMethods;
    }

    /**
     * Return any methods that should be called after the benchmark class is executed.
     */
    public function getAfterClassMethods()
    {
        return $this->afterClassMethods;
    }

    /**
     * Set any methods that should be called after the benchmark class is executed.
     *
     * @param array $afterClassMethods
     */
    public function setAfterClassMethods(array $afterClassMethods)
    {
        $this->afterClassMethods = $afterClassMethods;
    }

    public function getIterator()
    {
        return $this->subjects;
    }

    /**
     * @return ExecutorMetadata|null
     */
    public function getExecutor()
    {
        return $this->executorMetadata;
    }

    public function setExecutor(ExecutorMetadata $serviceMetadata)
    {
        $this->executorMetadata = $serviceMetadata;
    }
}
