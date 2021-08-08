<?php

namespace PhpBench\Config;

use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

class ConfigLoader
{
    /**
     * @var array
     */
    private $processors;

    /**
     * @var JsonParser
     */
    private $parser;


    public function __construct(JsonParser $parser, array $processors)
    {
        $this->processors = $processors;
        $this->parser = $parser;
    }

    public static function create(): self
    {
        return new self(new JsonParser(), []);
    }

    public function load(string $path): array
    {
        $configRaw = (string)file_get_contents($path);

        try {
            $this->parser->parse($configRaw);
        } catch (ParsingException $e) {
            echo 'Error parsing config file:' . PHP_EOL . PHP_EOL;
            echo $e->getMessage();

            exit(1);
        }

        return json_decode($configRaw, true);
    }
}
