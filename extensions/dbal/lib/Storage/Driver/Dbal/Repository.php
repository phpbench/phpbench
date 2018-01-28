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

use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Visitor\SqlVisitor;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Visitor\TokenValueVisitor;

/**
 * Class for retrieving data from the database.
 */
class Repository
{
    private $manager;
    private $sqlVisitor;
    private $tokenVisitor;

    public function __construct(ConnectionManager $manager, TokenValueVisitor $tokenVisitor, SqlVisitor $sqlVisitor = null)
    {
        $this->manager = $manager;
        $this->tokenVisitor = $tokenVisitor;
        $this->sqlVisitor = $sqlVisitor ?: new Visitor\SqlVisitor();
    }

    public function getIterationRows(Constraint $constraint)
    {
        $this->tokenVisitor->visit($constraint);
        list($sql, $values) = $this->sqlVisitor->visit($constraint);

        $conn = $this->manager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $rows;
    }

    public function getRunEnvInformationRows($runId)
    {
        $sql = 'SELECT * FROM environment WHERE run_id = ?';

        $conn = $this->manager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute([$runId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getGroups($subjectId)
    {
        $sql = <<<'EOT'
SELECT 
    sgroup
    FROM sgroup_subject
    WHERE sgroup_subject.subject_id = ?
EOT;

        $conn = $this->manager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute([$subjectId]);

        $groups = array_map(function ($value) {
            return $value[0];
        }, $stmt->fetchAll(\PDO::FETCH_NUM));

        return $groups;
    }

    public function getParameters($variantId)
    {
        $sql = <<<'EOT'
SELECT 
    pkey,
    value
    FROM parameter
    LEFT JOIN variant_parameter ON variant_parameter.parameter_id = parameter.id
    WHERE variant_parameter.variant_id = ?
EOT;

        $conn = $this->manager->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute([$variantId]);

        $parameters = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $data) {
            $parameters[$data['pkey']] = json_decode($data['value']);
        }

        return $parameters;
    }

    public function hasRun($runId)
    {
        return $this->getRunId($runId) ? true : false;
    }

    public function deleteRun($runId)
    {
        $conn = $this->manager->getConnection();
        $stmt = $conn->prepare('DELETE FROM run WHERE uuid = ?');
        $stmt->execute([$runId]);
    }

    public function getLatestRunUuid()
    {
        $conn = $this->manager->getConnection();
        $stmt = $conn->prepare('SELECT uuid FROM run ORDER BY id DESC LIMIT 1');
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getHistoryStatement()
    {
        $sql = <<<'EOT'
SELECT 
    run.uuid AS run_uuid, 
    run.date AS run_date,
    run.tag AS tag,
    environment.value AS vcs_branch,
    run.nb_subjects AS nb_subjects,
    run.nb_iterations AS nb_iterations,
    run.nb_revolutions AS nb_revolutions,
    run.min_time AS min_time,
    run.max_time AS max_time,
    run.mean_time AS mean_time,
    run.mean_rstdev AS mean_rstdev,
    run.total_time AS total_time
    FROM run
    LEFT OUTER JOIN environment ON environment.provider = "vcs" AND environment.run_id = run.id AND environment.ekey = "branch"
    ORDER BY run.date DESC
EOT;

        $conn = $this->manager->getConnection();
        $stmt = $conn->query($sql);

        return $stmt;
    }

    private function getRunId($runUuid)
    {
        $conn = $this->manager->getConnection();
        $stmt = $conn->prepare('SELECT id FROM run WHERE uuid = ?');
        $stmt->execute([$runUuid]);

        return $stmt->fetch();
    }
}
