<?php

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
                'Could not parse trace file "%s"',
                $path
            ));
        }

        $version = $matches[1];
        $traceEl->setAttribute('version', $matches[1]);

        $format = fgets($handle);
        preg_match('/^File format: (.*)$/', $format, $matches);
        $traceEl->setAttribute('format', $matches[1]);

        $start = fgets($handle);
        preg_match('/^TRACE START \[(.*)\]$/', $start, $matches);
        $traceEl->setAttribute('start', $matches[1]);
        $scopeEl = $traceEl;

        $tree = array();

        while ($line = fgets($handle)) {
            if (preg_match('/TRACE END\s+\[(.*)\]/', $line, $matches)) {
                $scopeEl->setAttribute('end', $matches[1]);
                break;
            }
            $parts = explode("\t", $line);
            $level = $parts[0];

            // '0' is entry, '1' is exit, 'R' is return
            if ($parts[2] == '1') {
                $scopeEl = $tree[$level];
                $scopeEl->setAttribute('end-time', $parts[3]);
                $scopeEl->setAttribute('end-memory', trim($parts[4]));
                $scopeEl = $scopeEl->parentNode;
                continue;
            } elseif ($parts[2] == 'R') {
                $scopeEl = $tree[$level];
                $scopeEl = $scopeEl->parentNode;
                continue;
            } elseif ($parts[2] == '') {
                continue;
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

            if (isset($parts[10])) {
                $entryEl->setAttribute('nb_params', $parts[10]);
            }

            $tree[$level] = $entryEl;
            $scopeEl = $entryEl;
        }

        return $dom;
    }
}
