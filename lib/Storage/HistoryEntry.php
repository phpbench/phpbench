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

namespace PhpBench\Storage;

use DateTime;
use PhpBench\Model\Tag;

/**
 * Represents a summary of a run. Used when listing the history.
 */
class HistoryEntry
{
    /**
     * @param ?string $runId
     * @param ?string $tag
     * @param ?string $branch
     * @param int $nbSubjects
     * @param int $nbIterations
     * @param int $nbRevolutions
     * @param int|float $minTime
     * @param int|float $maxTime
     * @param int|float $meanTime
     * @param int|float $meanRelStDev
     * @param int|float $totalTime
     */
    public function __construct(
        private                   $runId,
        private readonly DateTime $date,
        private                   $tag,
        private                   $branch,
        private                   $nbSubjects,
        private                   $nbIterations,
        private                   $nbRevolutions,
        private                   $minTime,
        private                   $maxTime,
        private                   $meanTime,
        private                   $meanRelStDev,
        private                   $totalTime
    ) {
    }

    /**
     * @return string|null
     */
    public function getRunId()
    {
        return $this->runId;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getTag(): ?Tag
    {
        return $this->tag ? new Tag($this->tag) : null;
    }

    /**
     * @return int
     */
    public function getNbSubjects()
    {
        return $this->nbSubjects;
    }

    /**
     * @return int
     */
    public function getNbIterations()
    {
        return $this->nbIterations;
    }

    /**
     * @return int
     */
    public function getNbRevolutions()
    {
        return $this->nbRevolutions;
    }

    /**
     * @return string|null
     */
    public function getVcsBranch()
    {
        return $this->branch;
    }

    /**
     * @return float|int
     */
    public function getMinTime()
    {
        return $this->minTime;
    }

    /**
     * @return float|int
     */
    public function getMaxTime()
    {
        return $this->maxTime;
    }

    /**
     * @return float|int
     */
    public function getMeanTime()
    {
        return $this->meanTime;
    }

    /**
     * @return float|int
     */
    public function getMeanRelStDev()
    {
        return $this->meanRelStDev;
    }

    /**
     * @return float|int
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }
}
