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

use Doctrine\DBAL\Schema\Schema as BaseSchema;

class Schema extends BaseSchema
{
    private $runTable;
    private $subjectTable;
    private $variantTable;
    private $parameterTable;

    public function __construct()
    {
        parent::__construct();
        $this->createRun();
        $this->createSubject();
        $this->createVariant();
        $this->createParameter();
        $this->createVariantParameter();
        $this->createGroupSubject();
        $this->createEnvironment();
        $this->createIteration();
        $this->createVersion();
    }

    private function createRun()
    {
        $table = $this->createTable('run');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('uuid', 'string');
        $table->addColumn('tag', 'string', ['notnull' => false]);
        $table->addColumn('date', 'datetime');
        $table->addColumn('nb_subjects', 'integer');
        $table->addColumn('nb_iterations', 'integer');
        $table->addColumn('nb_revolutions', 'integer');
        $table->addColumn('min_time', 'float');
        $table->addColumn('max_time', 'float');
        $table->addColumn('mean_time', 'float');
        $table->addColumn('mean_rstdev', 'float');
        $table->addColumn('total_time', 'float');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['uuid']);
        $this->runTable = $table;
    }

    private function createSubject()
    {
        $table = $this->createTable('subject');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('benchmark', 'string');
        $table->addColumn('name', 'string');
        $table->setPrimaryKey(['id']);
        $this->subjectTable = $table;
    }

    private function createVariant()
    {
        $table = $this->createTable('variant');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('run_id', 'integer');
        $table->addColumn('subject_id', 'integer');
        $table->addColumn('sleep', 'integer', ['notnull' => false]);
        $table->addColumn('output_time_unit', 'string', ['notnull' => false]);
        $table->addColumn('output_time_precision', 'string', ['notnull' => false]);
        $table->addColumn('output_mode', 'string', ['notnull' => false]);
        $table->addColumn('revolutions', 'integer');
        $table->addColumn('warmup', 'integer', ['notnull' => false]);
        $table->addColumn('retry_threshold', 'float', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            $this->runTable, ['run_id'], ['id'], ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $this->subjectTable, ['subject_id'], ['id'], ['onDelete' => 'CASCADE']
        );
        $this->variantTable = $table;
    }

    private function createVariantParameter()
    {
        $table = $this->createTable('variant_parameter');
        $table->addColumn('variant_id', 'integer');
        $table->addColumn('parameter_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->variantTable, ['variant_id'], ['id'], ['onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $this->parameterTable, ['parameter_id'], ['id'], ['onDelete' => 'CASCADE']
        );
    }

    private function createGroupSubject()
    {
        $table = $this->createTable('sgroup_subject');
        $table->addColumn('sgroup', 'string');
        $table->addColumn('subject_id', 'integer');
        $table->addForeignKeyConstraint(
            $this->subjectTable, ['subject_id'], ['id'], ['onDelete' => 'CASCADE']
        );
    }

    private function createEnvironment()
    {
        $table = $this->createTable('environment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('run_id', 'integer');
        $table->addColumn('provider', 'string');
        $table->addColumn('ekey', 'string');
        $table->addColumn('value', 'string');
        $table->addForeignKeyConstraint(
            $this->runTable, ['run_id'], ['id'], ['onDelete' => 'CASCADE']
        );
        $table->setPrimaryKey(['id']);
    }

    private function createParameter()
    {
        $table = $this->createTable('parameter');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('pkey', 'string');
        $table->addColumn('value', 'string');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['pkey', 'value']);
        $this->parameterTable = $table;
    }

    private function createIteration()
    {
        $table = $this->createTable('iteration');
        $table->addColumn('variant_id', 'integer');
        $table->addColumn('time', 'integer');
        $table->addColumn('memory', 'integer');
        $table->addColumn('reject_count', 'integer');
        $table->addForeignKeyConstraint(
            $this->variantTable, ['variant_id'], ['id'], ['onDelete' => 'CASCADE']
        );
    }

    private function createVersion()
    {
        $table = $this->createTable('version');
        $table->addColumn('phpbench_version', 'string');
        $table->addColumn('date', 'datetime');
    }
}
