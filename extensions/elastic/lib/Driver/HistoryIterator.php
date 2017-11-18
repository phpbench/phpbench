<?php

namespace PhpBench\Extensions\Elastic\Driver;

use PhpBench\Storage\HistoryIteratorInterface;
use PhpBench\Extensions\Elastic\Encoder\DocumentDecoder;
use DateTime;
use PhpBench\Storage\HistoryEntry;

class HistoryIterator implements HistoryIteratorInterface
{
    const SIZE = 100;
    const AGG_DATE = 'date';
    const AGG_CONTEXT = 'context';
    const AGG_ITERATIONS = 'nb_iterations';
    const AGG_REVOLUTIONS = 'nb_revolutions';
    const AGG_MIN = 'min';
    const AGG_MAX = 'max';
    const AGG_MEAN = 'mean';
    const AGG_RSTDEV = 'rstdev';
    const AGG_SUM = 'sum';
    const GROUP_SUITE = 'group_by_suite';
    const AGG_SUBJECTS = 'subjects';

    /**
     * @var ElasticClient
     */
    private $client;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var array
     */
    private $entries;

    public function __construct(ElasticClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        $this->valid();
        return $this->entries[$this->offset % self::SIZE];
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $this->offset++;
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->offset;
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        $index = $this->offset % self::SIZE;

        if ($index === 0) {
            $this->load();
        }

        if (isset($this->entries[$index])) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->offset = 0;
    }

    private function load()
    {
        $result = $this->client->search([
            'aggs' => [
                self::GROUP_SUITE => [
                    'terms' => [
                        'field' => 'suite',
                        'size' => 100,
                    ],
                    'aggs' => [
                        self::AGG_SUBJECTS => [
                            'terms' => [
                                'field' => 'name',
                            ],
                        ],
                        self::AGG_DATE => [
                            self::AGG_MIN => [ 'field' => self::AGG_DATE ],
                        ],
                        self::AGG_CONTEXT => [
                            self::AGG_MIN => [ 'field' => self::AGG_CONTEXT ],
                        ],
                        //'vcs_branch' => [
                        //    'min' => [ 'field' => 'env.vcs.branch' ],
                        //],
                        self::AGG_ITERATIONS => [
                            self::AGG_SUM => [ 'field' => self::AGG_ITERATIONS ],
                        ],
                        self::AGG_REVOLUTIONS => [
                            self::AGG_SUM => [ 'field' => 'revolutions' ],
                        ],
                        self::AGG_MIN => [
                            self::AGG_MIN => [ 'field' => 'stats.min' ],
                        ],
                        self::AGG_MAX => [
                            self::AGG_MIN => [ 'field' => 'stats.max' ],
                        ],
                        self::AGG_MEAN => [
                            self::AGG_MIN => [ 'field' => 'stats.mean' ],
                        ],
                        self::AGG_RSTDEV => [
                            'avg' => [ 'field' => 'stats.rstdev' ],
                        ],
                        self::AGG_SUM => [
                            'avg' => [ 'field' => 'stats.sum' ],
                        ],
                    ],
                ],
            ]
        ]);

        $this->entries = array_map(function (array $data) {
            return new HistoryEntry(
                $data['key'],
                new DateTime($data[self::AGG_DATE]['value_as_string']),
                $data[self::AGG_CONTEXT]['value'],
                '', // VCS Branch
                count($data[self::AGG_SUBJECTS]['buckets']),
                $data[self::AGG_ITERATIONS]['value'],
                $data[self::AGG_REVOLUTIONS]['value'],
                $data[self::AGG_MIN]['value'],
                $data[self::AGG_MAX]['value'],
                $data[self::AGG_MEAN]['value'],
                $data[self::AGG_RSTDEV]['value'],
                $data[self::AGG_SUM]['value']
            );
        }, $result['aggregations'][self::GROUP_SUITE]['buckets']);
    }
}
