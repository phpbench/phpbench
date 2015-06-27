<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Cellular\Step;

use DTL\Cellular\Workspace;
use DTL\Cellular\Table;
use DTL\Cellular\Calculator;
use PhpBench\Report\Cellular\Step;
use DTL\Cellular\Row;

class ExplodeStep implements Step
{
    private $attributes;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function step(Workspace $workspace)
    {
        $workspace
            ->each(function (Table $table) {
                $table->partition(function (Row $row) {
                    $key = array();
                    foreach ($this->attributes as $attribute) {
                        $key[] = json_encode($row[$attribute]->getValue());
                    }

                    return implode('', $key);
                });
            });
        $workspace->materialize();
        $workspace->each(function (Table $table) {
            $key = array();
            foreach ($this->attributes as $attribute) {
                $row = $table->first();
                $key[] = json_encode($row[$attribute]->getValue());
            }
            $table->setAttribute('description', sprintf(
                'Explode: %s %s', json_encode($this->attributes), implode('/', $key)
            ));
        });
    }
}
