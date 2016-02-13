<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Json;

use Seld\JsonLint\JsonParser;

/**
 * Decodes JSON to an array.
 *
 * Accepts non-strict "JSON":
 *
 *     - {this: ["is": {where: "sparta"}]} # quoteless keys are allowed
 *     - this: ["is": {where: "sparta"}]   # enclosing braces can be omitted
 *
 * Lints the JSON using the JsonParser.
 */
class JsonDecoder
{
    public function __construct()
    {
        $this->parser = new JsonParser();
    }

    /**
     * Normalize, parse and decode the given JSON(ish) encoded string into
     * an array.
     *
     * @param string $jsonString
     *
     * @return array
     */
    public function decode($jsonString)
    {
        $jsonString = $this->normalize($jsonString);
        $this->parser->parse($jsonString);

        return json_decode($jsonString, true);
    }

    /**
     * Taken from: https://gist.github.com/larruda/967110d74d98c1cd4ee1.
     */
    private function normalize($jsonString)
    {
        if (!is_string($jsonString)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected a string, got a "%s"',
                is_object($jsonString) ? get_class($jsonString) : gettype($jsonString)
            ));
        }

        if (substr($jsonString, 0, 1) !== '{') {
            $jsonString = '{' . $jsonString;
            $jsonString = $jsonString . '}';
        }

        $jsonString = preg_replace(
            '{(\s*?\{\s*?|\s*?,\s*?)([\'"])?([\$\.a-zA-Z0-9\[\]\_]+)([\'"])?:}',
            '\1"\3":',
            $jsonString
        );

        return $jsonString;
    }
}
