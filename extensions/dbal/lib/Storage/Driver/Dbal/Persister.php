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

namespace PhpBench\Extensions\Dbal\Storage\Driver\Dbal;

use Doctrine\DBAL\Connection;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\RejectionCountResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\SuiteCollection;

class Persister
{
    private $manager;

    public function __construct(ConnectionManager $manager)
    {
        $this->manager = $manager;
    }

    public function persist(SuiteCollection $collection)
    {
        $conn = $this->manager->getConnection();

        foreach ($collection->getSuites() as $suite) {
            $summary = $suite->getSummary();
            $id = $this->insertUpdate($conn, 'run', [
                'uuid' => $suite->getUuid(),
                'tag' => $suite->getTag(),
                'date' => $suite->getDate()->format('Y-m-d H:i:s'),
                'nb_subjects' => $summary->getNbSubjects(),
                'nb_iterations' => $summary->getNbIterations(),
                'nb_revolutions' => $summary->getNbRevolutions(),
                'min_time' => $summary->getMinTime(),
                'max_time' => $summary->getMaxTime(),
                'mean_time' => $summary->getMeanTime(),
                'mean_rstdev' => $summary->getMeanRelStDev(),
                'total_time' => $summary->getTotalTime(),
            ]);

            $envData = [];
            foreach ($suite->getEnvInformations() as $information) {
                foreach ($information as $key => $value) {
                    $envData[] = [
                        'run_id' => $id,
                        'provider' => $information->getName(),
                        'ekey' => $key,
                        'value' => $value,
                    ];
                }
            }
            $this->insertMultiple($conn, 'environment', $envData);

            $iterationDatas = [];
            $parameterAssocs = [];
            foreach ($suite->getBenchmarks() as $benchmark) {
                foreach ($benchmark->getSubjects() as $subject) {
                    $data = [
                        'benchmark' => $benchmark->getClass(),
                        'name' => $subject->getName(),
                    ];
                    $subjectId = $this->getSubjectId($conn, $data['benchmark'], $data['name']);
                    $subjectId = $this->insertUpdate($conn, 'subject', $data, $subjectId);

                    foreach ($subject->getGroups() as $groupName) {
                        $this->associateGroup($conn, $subjectId, $groupName);
                    }

                    foreach ($subject->getVariants() as $variant) {
                        $data = [
                            'revolutions' => $variant->getRevolutions(),
                            'retry_threshold' => $subject->getRetryThreshold(),
                            'output_time_unit' => $subject->getOutputTimeUnit(),
                            'output_time_precision' => $subject->getOutputTimePrecision(),
                            'output_mode' => $subject->getOutputMode(),
                            'sleep' => $subject->getSleep(),
                            'warmup' => $variant->getWarmup(),
                            'subject_id' => $subjectId,
                            'run_id' => $id,
                        ];
                        $variantId = $this->insertUpdate($conn, 'variant', $data);

                        foreach ($variant->getParameterSet() as $key => $value) {
                            $value = json_encode($value);
                            $parameterId = $this->getOrCreateParameter($conn, $key, $value);
                            $parameterAssocs[] = [
                                'variant_id' => $variantId,
                                'parameter_id' => $parameterId,
                            ];
                        }

                        foreach ($variant as $iteration) {
                            $iterationDatas[] = [
                                'time' => $iteration->getMetric(TimeResult::class, 'net', null),
                                'memory' => $iteration->getMetricOrDefault(MemoryResult::class, 'peak', -1),
                                'reject_count' => $iteration->getMetricOrDefault(RejectionCountResult::class, 'count', 0),
                                'variant_id' => $variantId,
                            ];
                        }
                    }
                }
            }

            // insert the iterations and variant parameter relations in batch
            $this->insertMultiple($conn, 'iteration', $iterationDatas);
            $this->insertMultiple($conn, 'variant_parameter', $parameterAssocs);
        }
    }

    private function getSubjectId(Connection $conn, $benchmarkClass, $subjectName)
    {
        $stmt = $conn->prepare('SELECT id FROM subject WHERE benchmark = ? AND name = ?');
        $stmt->execute([$benchmarkClass, $subjectName]);

        return $stmt->fetchColumn();
    }

    private function getOrCreateParameter(Connection $conn, $key, $value)
    {
        $stmt = $conn->prepare('SELECT id FROM parameter WHERE pkey = ? AND value = ?');
        $stmt->execute([$key, $value]);
        $identifier = $stmt->fetchColumn();

        if (false !== $identifier) {
            return $identifier;
        }

        $stmt = $conn->prepare('INSERT INTO parameter (pkey, value) VALUES (?, ?)');
        $stmt->execute([$key, $value]);

        return $conn->lastInsertId();
    }

    private function associateGroup(Connection $conn, $subjectId, $groupName)
    {
        $stmt = $conn->prepare('SELECT subject_id FROM sgroup_subject WHERE subject_id = ? AND sgroup = ?');
        $stmt->execute([$subjectId, $groupName]);

        if (!$stmt->fetchColumn()) {
            $this->insertUpdate($conn, 'sgroup_subject', [
                'subject_id' => $subjectId,
                'sgroup' => $groupName,
            ]);
        }
    }

    private function insertUpdate(Connection $conn, $tableName, array $data, $identifier = null)
    {
        if (is_numeric($identifier)) {
            $identifier = ['id', $identifier];
        }

        $columnNames = array_keys($data);
        $values = array_values($data);

        if ($identifier) {
            $updateSql = implode(', ', array_map(function ($value) {
                return sprintf('%s = ?', $value);
            }, $columnNames));
            $stmt = $conn->prepare(sprintf(
                'UPDATE %s SET %s WHERE ? = ?',
                $tableName, $updateSql
            ));
            $values[] = $identifier[0];
            $values[] = $identifier[1];
            $stmt->execute($values);

            return $identifier[1];
        }

        $insertSql = implode(', ', $columnNames);
        $placeholders = implode(', ', array_fill(0, count($columnNames), '?'));

        $stmt = $conn->prepare(sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $tableName,
            $insertSql,
            $placeholders
        ));
        $stmt->execute($values);

        return $conn->lastInsertId();
    }

    private function insertMultiple(Connection $conn, $tableName, array $datas)
    {
        if (empty($datas)) {
            return;
        }

        $dataSets = array_values($datas);

        $firstData = current($datas);
        $columnNames = array_keys($firstData);

        $placeholders = sprintf('(%s)', implode(', ', array_fill(0, count($columnNames), '?')));
        $values = [];

        foreach ($dataSets as $dataSet) {
            $values = array_merge($values, array_values($dataSet));
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES %s',
            $tableName,
            implode(', ', $columnNames),
            implode(', ', array_fill(0, count($dataSets), $placeholders))
        );
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
    }
}
