<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return array(
    'console' => array(
        'renderer' => 'console',
    ),
    'html' => array(
        'renderer' => 'xslt',
        'template' => __DIR__ . '/../../../Report/Renderer/templates/html.xsl',
    ),
    'markdown' => array(
        'renderer' => 'xslt',
        'template' => __DIR__ . '/../../../Report/Renderer/templates/markdown.xsl',
    ),
    'delimited' => array(
        'renderer' => 'delimited',
    ),
    'csv' => array(
        'renderer' => 'delimited',
        'delimiter' => ',',
    ),
    'debug' => array(
        'renderer' => 'debug',
    ),
);
