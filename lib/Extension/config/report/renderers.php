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
    'html' => [
        'renderer' => 'xslt',
        'template' => __DIR__ . '/../../../Report/Renderer/templates/html.xsl',
    ],
    'markdown' => [
        'renderer' => 'xslt',
        'template' => __DIR__ . '/../../../Report/Renderer/templates/markdown.xsl',
    ],
    'csv' => [
        'renderer' => 'delimited',
        'delimiter' => ',',
    ],
];
