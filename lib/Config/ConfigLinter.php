<?php

namespace PhpBench\Config;

use PhpBench\Config\Exception\LintError;

interface ConfigLinter
{
    /**
     * @throws LintError
     */
    public function lint(string $path, string $config): void;
}
