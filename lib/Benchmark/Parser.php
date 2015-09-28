<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

class Parser
{
    public function parseDoc($doc, array $defaults = array())
    {
        $lines = explode(PHP_EOL, $doc);

        $meta = array(
            'beforeMethod' => array(),
            'afterMethod' => array(),
            'paramProvider' => array(),
            'iterations' => array(),
            'group' => array(),
            'revs' => array(),
            'skip' => false,
        );

        // singular annotations
        foreach (array('iterations') as $key) {
            if (isset($defaults[$key]) && $defaults[$key]) {
                $meta[$key][] = $defaults[$key];
            }
        }

        // plural annotations
        foreach (array('afterMethod', 'beforeMethod', 'paramProvider', 'revs', 'group') as $key) {
            if (isset($defaults[$key]) && $defaults[$key]) {
                $meta[$key] = $defaults[$key];
            }
        }

        foreach ($lines as $line) {
            if (preg_match('{@skip.*$}', $line)) {
                $meta['skip'] = true;
                continue;
            }

            if (!preg_match('{@([a-zA-Z0-9]+)\s+(.*)$}', $line, $matches)) {
                continue;
            }

            $annotationName = $matches[1];
            $annotationValue = $matches[2];

            if (!isset($meta[$annotationName])) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown annotation "%s"',
                    $annotationName
                ));
            }

            $meta[$annotationName][] = $annotationValue;
        }

        // Do not allow these annotations to be redelared twice in the same docblock
        foreach (array('iterations') as $key) {
            // allow overriding single values
            if (count($meta[$key] == 2) && !empty($defaults[$key]) && count($defaults[$key]) == 1) {
                $value = array_pop($meta[$key]);
                $meta[$key] = array($value);
            }

            if (count($meta[$key]) > 1) {
                throw new \InvalidArgumentException(sprintf(
                    'Cannot have more than one "@%s" annotation', $key
                ));
            }
        }

        $iterations = $meta['iterations'];
        $meta['iterations'] = empty($iterations) ? 1 : (int) reset($iterations);

        return $meta;
    }
}
