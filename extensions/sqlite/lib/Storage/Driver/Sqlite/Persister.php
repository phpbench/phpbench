<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite;

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
            $runId = $this->insertUpdate($conn, 'run', [
                'context' => $suite->getContextName(),
                'date' => $suite->getDate()->format('Y-m-d H:i:s'),
            ]);

            foreach ($suite->getEnvInformations() as $information) {
                foreach ($information as $key => $value) {
                    $this->insertUpdate($conn, 'environment', [
                        'run_id' => $runId,
                        'provider' => $information->getName(),
                        'key' => $key,
                        'value' => $value,
                    ]);
                }
            }

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
                            'run_id' => $runId,
                        ];
                        $variantId = $this->insertUpdate($conn, 'variant', $data);

                        foreach ($variant->getParameterSet() as $key => $value) {
                            $value = json_encode($value);
                            $parameterId = $this->getOrCreateParameter($conn, $key, $value);
                            $this->insertUpdate($conn, 'variant_parameter', [
                                'variant_id' => $variantId,
                                'parameter_id' => $parameterId,
                            ]);
                        }

                        $datas = [];
                        foreach ($variant as $iteration) {
                            $datas[] = [
                                'time' => $iteration->getTime(),
                                'memory' => $iteration->getMemory(),
                                'reject_count' => $iteration->getRejectionCount(),
                                'variant_id' => $variantId,
                            ];
                        }

                        $this->insertMultiple($conn, 'iteration', $datas);
                    }
                }
            }
        }
    }

    private function getSubjectId(\PDO $conn, $benchmarkClass, $subjectName)
    {
        $stmt = $conn->prepare('SELECT id FROM subject WHERE benchmark = ? AND name = ?');
        $stmt->execute([$benchmarkClass, $subjectName]);

        return $stmt->fetchColumn();
    }

    private function getOrCreateParameter(\PDO $conn, $key, $value)
    {
        $stmt = $conn->prepare('SELECT id FROM parameter WHERE key = ? AND value = ?');
        $stmt->execute([$key, $value]);
        $identifier = $stmt->fetchColumn();

        if (false !== $identifier) {
            return $identifier;
        }

        $stmt = $conn->prepare('INSERT INTO parameter (key, value) VALUES (?, ?)');
        $stmt->execute([$key, $value]);

        return $conn->lastInsertId();
    }

    private function associateGroup(\PDO $conn, $subjectId, $groupName)
    {
        $stmt = $conn->prepare('SELECT id FROM sgroup WHERE name = ?');
        $stmt->execute([$groupName]);
        $groupId = $stmt->fetchColumn();

        if (!$groupId) {
            $groupId = $this->insertUpdate($conn, 'sgroup', [
                'name' => $groupName,
            ]);
        }

        $stmt = $conn->prepare('SELECT subject_id FROM sgroup_subject WHERE subject_id = ? AND sgroup_id = ?');
        $stmt->execute([$subjectId, $groupId]);

        if (!$stmt->fetchColumn()) {
            $this->insertUpdate($conn, 'sgroup_subject', [
                'subject_id' => $subjectId,
                'sgroup_id' => $groupId,
            ]);
        }
    }

    private function insertUpdate(\PDO $conn, $tableName, array $data, $identifier = null)
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

    private function insertMultiple(\PDO $conn, $tableName, array $datas)
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
