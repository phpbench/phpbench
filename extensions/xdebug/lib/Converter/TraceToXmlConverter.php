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

namespace PhpBench\Extensions\XDebug\Converter;

use PhpBench\Dom\Document;

class TraceToXmlConverter
{
    public function convert($path)
    {
        $dom = new Document(1.0);
        $traceEl = $dom->createRoot('trace');
        $handle = fopen($path, 'r');
        $version = fgets($handle);

        if (!preg_match('/^Version: (.*)$/', $version, $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected "Version" in trace "%s"',
                $path
            ));
        }

        $version = $matches[1];
        $traceEl->setAttribute('version', $matches[1]);

        $format = fgets($handle);

        if (!preg_match('/^File format: (.*)$/', $format, $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected "File format" in trace "%s"',
                $path
            ));
        }

        $traceEl->setAttribute('format', $matches[1]);

        $start = fgets($handle);

        if (!preg_match('/^TRACE START \[(.*)\]$/', $start, $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected "TRACE START" in "%s"',
                $path
            ));
        }
        $traceEl->setAttribute('start', $matches[1]);
        $scopeEl = $traceEl;

        $tree = [];
        $lineNb = 3;
        $buffer = null;

        while ($line = fgets($handle)) {
            $lineNb++;

            if (preg_match('/TRACE END\s+\[(.*)\]/', $line, $matches)) {
                $scopeEl->setAttribute('end-time', $last[3]);
                $scopeEl->setAttribute('end-memory', trim($last[4]));
                $scopeEl->setAttribute('end', $matches[1]);

                break;
            }

            $parts = explode("\t", $line);
            $level = $parts[0];

            // '0' is entry, '1' is exit, 'R' is return
            if (isset($parts[2]) && $parts[2] == '1') {
                $scopeEl = $tree[$level];
                $scopeEl->setAttribute('end-time', $parts[3]);
                $scopeEl->setAttribute('end-memory', trim($parts[4]));
                $scopeEl = $scopeEl->parentNode;

                continue;
            } elseif (isset($parts[2]) && $parts[2] == 'R') {
                $scopeEl = $tree[$level];
                $scopeEl = $scopeEl->parentNode;

                continue;
            } elseif (isset($parts[2]) && $parts[2] == '') {
                $last = $parts;

                continue;
            }

            if (count($parts) < 9) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected at least 9 fields, got "%s" in "%s:%s"',
                    count($parts),
                    $path,
                    $lineNb
                ));
            }

            $entryEl = $scopeEl->appendElement('entry');
            $entryEl->setAttribute('level', $parts[0]);
            $entryEl->setAttribute('func_nb', $parts[1]);
            $entryEl->setAttribute('start-time', $parts[3]);
            $entryEl->setAttribute('start-memory', $parts[4]);
            $entryEl->setAttribute('function', $parts[5]);
            $entryEl->setAttribute('is_user', $parts[6]);
            $entryEl->setAttribute('include', $parts[7]);
            $entryEl->setAttribute('filename', $parts[8]);
            $entryEl->setAttribute('line', $parts[9]);

            // parse number of parameters if set.
            if (isset($parts[10])) {
                $entryEl->setAttribute('nb_params', $parts[10]);
            }

            // parse arguments
            $i = 11;

            while (isset($parts[$i])) {
                /** @var \DOMElement $argEl */
                $argEl = $entryEl->appendElement('arg');
                $argEl->appendChild(
                    $dom->createTextNode(html_entity_decode($parts[$i]))
                );
                $i++;
            }

            $tree[$level] = $entryEl;
            $scopeEl = $entryEl;
        }

        return $dom;
    }
}
