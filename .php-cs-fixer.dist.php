<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$finder = Finder::create()
    ->in([
        __DIR__ . '/lib',
        __DIR__ . '/tests',
        __DIR__ . '/extensions',
    ])
    ->exclude([
        'Attributes'
    ])
;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'void_return' => true,
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => null
            ],
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'break',
                'continue',
                'declare',
                'default',
                'do',
                'exit',
                'for',
                'foreach',
                'goto',
                'if',
                'include',
                'include_once',
                'require',
                'require_once',
                'return',
                'switch',
                'throw',
                'try',
                'while',
                'yield',
            ],
        ],
        'concat_space' => false,
        'no_unused_imports' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'phpdoc_align' => [],
        'phpdoc_indent' => false,
        'phpdoc_separation' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true
        ],
        'fully_qualified_strict_types' => true,
    ])
    ->setFinder($finder)
    ;
