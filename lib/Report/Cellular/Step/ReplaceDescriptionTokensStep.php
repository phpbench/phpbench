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

use DTL\Cellular\Calculator;
use DTL\Cellular\Table;
use PhpBench\Report\Cellular\Step;
use DTL\Cellular\Workspace;

/**
 * Replace the tokens in the table descriptions with subject parameters
 */
class ReplaceDescriptionTokensStep implements Step
{
    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            if (!$table->hasAttribute('parameters')) {
                return;
            }
            $parameters = $table->getAttribute('parameters');

            $description = preg_replace_callback('{{(.+?)}}', function ($matches) use ($parameters) {
                if (isset($parameters[$matches[1]])) {
                    return json_encode($parameters[$matches[1]]);
                }

                return $matches[0];
            }, $table->getAttribute('description'));
            $table->setAttribute('description', $description);
        });
    }
}
