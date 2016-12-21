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

return [
    'console' => [
        'renderer' => 'console',
    ],
    'html' => [
        'renderer' => 'xslt',
        'template' => __DIR__ . '/../../../Report/Renderer/templates/html.xsl',
    ],
    'markdown' => [
        'renderer' => 'xslt',
        'template' => __DIR__ . '/../../../Report/Renderer/templates/markdown.xsl',
    ],
    'delimited' => [
        'renderer' => 'delimited',
    ],
    'csv' => [
        'renderer' => 'delimited',
        'delimiter' => ',',
    ],
    'debug' => [
        'renderer' => 'debug',
    ],
];
