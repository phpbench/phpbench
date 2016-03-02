<?php

namespace PhpBench\Storage\Driver\Xml;

use PhpBench\Model\Suite;

class XmlDriverUtil
{
    public static function getFilenameForSuite(Suite $suite)
    {
        return sprintf(
            '%s-%s-%s-%s.xml',
            $suite->getDate()->format('YmdHis'),
            uniqid(), 
            $suite->getVcsBranch(),
            $suite->getContextName()
        );
    }

    public static function parseFilename($filename)
    {
        // remove XML extension
        if (substr($filename, -4) !== '.xml') {
            throw new \InvalidArgumentException(sprintf(
                'Expected .xml extension when parsing filename "%s"', $filename
            ));

        }

        $filename = substr($filename, 0, -4);

        $parts = explode('-', $filename);

        return [
            'id' => $parts[1],
            'date' => new \DateTime($parts[0]),
            'vcs_branch' => isset($parts[2]) ? $parts[2] : null,
            'context' => isset($parts[3]) ? $parts[3] : null,
        ];
    }
}
