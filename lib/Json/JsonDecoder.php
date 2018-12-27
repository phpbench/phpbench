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
    /**
     * @var JsonParser
     */
    private $parser;

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
     * Allow "non-strict" JSON - i.e. if no quotes are provided then try and
     * add them.
     */
    private function normalize($jsonString)
    {
        if (!is_string($jsonString)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected a string, got "%s"',
                gettype($jsonString)
            ));
        }
        $chars = str_split($jsonString);
        $inRight = $inQuote = $inFakeQuote = false;
        $fakeQuoteStart = null;

        if (empty($chars)) {
            return;
        }

        if ($chars[0] !== '{') {
            array_unshift($chars, '{');
            $chars[] = '}';
        }

        for ($index = 0; $index < count($chars); $index++) {
            $char = $chars[$index];
            $prevChar = isset($chars[$index - 1]) ? $chars[$index - 1] : null;

            if (!$inQuote && $prevChar == ':') {
                $inRight = true;
            }

            if (!$inQuote && preg_match('{\{,}', $char)) {
                $inRight = false;
            }

            // detect start of unquoted string
            if (!$inQuote && preg_match('{[\$a-zA-Z0-9]}', $char)) {
                array_splice($chars, $index, 0, '"');
                $fakeQuoteStart = $index;
                $index++;
                $inQuote = $inFakeQuote = true;

                continue;
            }

            // if we added a "fake" quote, look for the end of the unquoted string
            if ($inFakeQuote && preg_match('{[\s:\}\],]}', $char)) {

                // if we are on the left side, then "]" is OK.
                if (!$inRight && $char === ']') {
                    continue;
                }

                if ($inRight) {

                    // extract the right hand value
                    $string = implode('', array_slice($chars, $fakeQuoteStart + 1, $index - 1 - $fakeQuoteStart));

                    // if it is a number, then we don't quote it
                    if (is_numeric($string)) {
                        unset($chars[$fakeQuoteStart]);
                        $chars = array_values($chars);
                        $inQuote = $inFakeQuote = false;
                        $index--;

                        continue;
                    }

                    // if it is a boolean, then we don't quote it
                    if (in_array($string, ['true', 'false'])) {
                        unset($chars[$fakeQuoteStart]);
                        $chars = array_values($chars);
                        $inQuote = $inFakeQuote = false;
                        $index--;

                        continue;
                    }
                }

                // add the ending quote
                array_splice($chars, $index, 0, '"');
                $index++;
                $inQuote = $inFakeQuote = false;

                continue;
            }

            // enter standard quote mode
            if (!$inQuote && $char === '"') {
                $inQuote = true;

                continue;
            }

            // if we are in quote mode and encounter a closing quote, and the last character
            // was not the escape character
            if ($inQuote && $char === '"' && $prevChar !== '\\') {
                $inQuote = $inFakeQuote = false;

                continue;
            }
        }

        // if we were in a fake quote and hit the end, then add the closing quote
        if ($inFakeQuote) {
            $chars[] = '"';
        }

        $normalized = implode('', $chars);

        return $normalized;
    }
}
