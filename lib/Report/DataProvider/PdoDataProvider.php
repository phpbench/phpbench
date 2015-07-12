<?php

namespace PhpBench\Report\DataProvider;

use PhpBench\Report\DataProvider;
use PhpBench\Result\SuiteResult;

class PdoDataProvider implements DataProvider
{
    private $connection;
    private $queries = array();

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function setQueries(array $queries = array())
    {
        $this->queries = $queries;
    }

    public function provide(SuiteResult $result)
    {
        $this->initDatabase($result);
        $datas = array();

        foreach ($this->queries as $query) {
            $stmt = $this->connection->prepare($query);
            if (false === $stmt) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not execute SQL statement: "%s"',
                    $query
                ));
            }
            $stmt->execute();

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $datas[] = $data;
        }

        return $datas;
    }

    private function initDatabase(SuiteResult $result)
    {
        $status = $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS suite (
                id INT,
                class VARCHAR(255),
                subject VARCHAR(255),
                description TEXT,
                parameters TEXT,
                revs INTEGER,
                time INTEGER,
                memory INTEGER,
                memory_diff INTEGER
            )
            '
        );

        if (false === $status) {
            $info = $this->connection->errorInfo();
            throw new \RuntimeException(sprintf(
                'Could not create table: [%s] %s',
                $info[0], $info[2]
            ));
        }

        $index = 0;
        foreach ($result->getBenchmarkResults() as $benchmarkResult) {
            foreach ($benchmarkResult->getSubjectResults() as $subjectResult) {
                foreach ($subjectResult->getIterationsResults() as $iterationsResult) {
                    foreach ($iterationsResult->getIterationResults() as $iterationResult) {
                        $sql = 'INSERT INTO suite (id, class, subject, description, parameters, revs, time, memory, memory_diff) ' .
                            'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';

                        $stmt = $this->connection->prepare($sql);

                        if (false === $stmt) {
                            $info = $this->connection->errorInfo();
                            throw new \RuntimeException(sprintf(
                                'Could insert benchmarking data: [%s] %s',
                                $info[0], $info[2]
                            ));
                        }

                        $res = $stmt->execute(array(
                            $index, 
                            $benchmarkResult->getClass(), 
                            $subjectResult->getName(),
                            $subjectResult->getDescription(),
                            json_encode($iterationsResult->getParameters()),
                            $iterationResult->get('revs'),
                            $iterationResult->get('time'),
                            $iterationResult->get('memory'),
                            $iterationResult->get('memory_diff')
                        ));

                        if (false === $res) {
                            $info = $this->connection->errorInfo();
                            throw new \RuntimeException(sprintf(
                                'Could insert benchmarking data: [%s] %s',
                                $info[0], $info[2]
                            ));
                        }

                        $index++;
                    }
                }
            }
        }
    }
}
