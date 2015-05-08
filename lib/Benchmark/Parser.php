<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

class Parser
{
    public function parseMethodDoc($methodDoc)
    {
        $lines = explode(PHP_EOL, $methodDoc);
        $meta = array();

        $meta = array(
            'beforeMethod' => array(),
            'paramProvider' => array(),
            'iterations' => array(),
            'description' => array(),
        );

        foreach ($lines as $line) {
            if (!preg_match('{@([a-zA-Z0-9]+)\s+(.*)$}', $line, $matches)) {
                continue;
            }

            $annotationName = $matches[1];
            $annotationValue = $matches[2];

            if (!isset($meta[$annotationName])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unknown annotation "%s"',
                    $annotationName
                ));
            }

            $meta[$annotationName][] = $annotationValue;
        }

        if (count($meta['description']) > 1) {
            throw new Exception\InvalidArgumentException(
                'Method "%s" in bench case "%s" cannot have more than one description'
            );
        }

        if (count($meta['iterations']) > 1) {
            throw new Exception\InvalidArgumentException(
                'Cannot have more than one iterations declaration'
            );
        }

        $meta['description'] = reset($meta['description']);
        $iterations = $meta['iterations'];
        $meta['iterations'] = empty($iterations) ? 1 : (int) reset($iterations);

        return $meta;
    }
}
