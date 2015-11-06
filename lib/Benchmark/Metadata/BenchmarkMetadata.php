<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Metadata;

/**
 * Benchmark metadata class.
 */
class BenchmarkMetadata extends AbstractMetadata
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var SubjectMetadata[]
     */
    private $subjectMetadatas = array();

    /**
     * @param mixed $path
     * @param mixed $class
     */
    public function __construct($path, $class)
    {
        $this->path = $path;
        parent::__construct($class);
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
     * Set the metadata for the given subject. Will replace
     * any subject with the same name.
     *
     * @param SubjectMetadata $subjectMetadata
     */
    public function setSubjectMetadata(SubjectMetadata $subjectMetadata)
    {
        $this->subjectMetadatas[$subjectMetadata->getName()] = $subjectMetadata;
    }

    /**
     * Get or create a new SubjectMetadata instance with the given name.
     *
     * @param string $name
     *
     * @return SubjectMetadata
     */
    public function getOrCreateSubjectMetadata($name)
    {
        if (isset($this->subjectMetadatas[$name])) {
            return $this->subjectMetadatas[$name];
        }

        return new SubjectMetadata($this, $name);
    }

    /**
     * Get the subject metadata instances for this benchmark metadata.
     *
     * @return SubjectMetadata[]
     */
    public function getSubjectMetadatas()
    {
        return $this->subjectMetadatas;
    }

    /**
     * Remove all subjects whose name is not in the given list.
     *
     * @param array $subjectNames
     */
    public function filterSubjectNames(array $filters)
    {
        foreach (array_keys($this->subjectMetadatas) as $subjectName) {
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
                unset($this->subjectMetadatas[$subjectName]);
            }
        }
    }

    /**
     * Remve all the subjects which are not contained in the given list of groups.
     *
     * @param string[]
     */
    public function filterSubjectGroups(array $groups)
    {
        foreach ($this->subjectMetadatas as $subjectName => $subjectMetadata) {
            if (0 === count(array_intersect($subjectMetadata->getGroups(), $groups))) {
                unset($this->subjectMetadatas[$subjectName]);
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
        return 0 !== count($this->subjectMetadatas);
    }
}
