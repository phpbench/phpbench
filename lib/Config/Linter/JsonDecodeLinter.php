<?php

namespace PhpBench\Config\Linter;

use function json_last_error;
use function json_last_error_msg;
use PhpBench\Config\ConfigLinter;
use PhpBench\Config\Exception\LintError;

class JsonDecodeLinter implements ConfigLinter
{
    public function lint(string $path, string $config): void
    {
        json_decode($config);

        if (json_last_error()) {
            throw new LintError(sprintf(
                'Json decode returned an error: "%s"', json_last_error_msg()
            ));
        }
    }
}
