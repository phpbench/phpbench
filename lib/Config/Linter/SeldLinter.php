<?php

namespace PhpBench\Config\Linter;

use PhpBench\Config\ConfigLinter;
use PhpBench\Config\Exception\LintError;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

class SeldLinter implements ConfigLinter
{
    /**
     * @var JsonParser
     */
    private $linter;

    public function __construct()
    {
        $this->linter = new JsonParser();
    }
    /**
     * {@inheritDoc}
     */
    public function lint(string $path, string $config): void
    {
        try {
            $this->linter->parse($config);
        } catch (ParsingException $e) {
            throw new LintError(sprintf(
                'Lint failed for "%s": %s',
                $path,
                $e->getMessage()
            ), 0, $e);
        }
    }
}
