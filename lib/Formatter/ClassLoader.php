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

namespace PhpBench\Formatter;

use Seld\JsonLint\JsonParser;

/**
 * Loads a PHPBench formatting JSON class file.
 *
 * Class files contain a list of "classes" each of which has
 * a set of configured formatters.
 *
 * Classes are analagous to CSS classes.
 */
class ClassLoader
{
    private $parser;

    public function __construct(JsonParser $parser = null)
    {
        $this->parser = $parser ?: new JsonParser();
    }

    public function load($filename)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(sprintf(
                'Class file "%s" does not exist.',
                $filename
            ));
        }

        $contents = file_get_contents($filename);

        if ($error = $this->parser->lint($contents)) {
            throw $error;
        }

        return json_decode($contents, true);
    }
}
