<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Metadata;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\DocParser as DoctrineDocParser;

/**
 * PHPBench doc parser, uses the Doctrine DocParser.
 */
class DocParser
{
    private $docParser;
    private $imports = array(
        'BeforeMethods' => 'PhpBench\Benchmark\Metadata\Annotations\BeforeMethods',
        'AfterMethods' => 'PhpBench\Benchmark\Metadata\Annotations\AfterMethods',
        'ParamProviders' => 'PhpBench\Benchmark\Metadata\Annotations\ParamProviders',
        'Groups' => 'PhpBench\Benchmark\Metadata\Annotations\Groups',
        'Iterations' => 'PhpBench\Benchmark\Metadata\Annotations\Iterations',
        'Revs' => 'PhpBench\Benchmark\Metadata\Annotations\Revs',
    );

    public function __construct()
    {
        $this->docParser = new DoctrineDocParser();
        $imports = array();
        foreach ($this->imports as $key => $value) {
            $imports[strtolower($key)] = $value;
        }

        $this->docParser->setImports($imports);
    }

    /**
     * Delegates to the doctrine DocParser but catches annotation not found errors and throws
     * something useful.
     *
     * @see \Doctrine\Common\Annotations\DocParser
     */
    public function parse($input, $context = '')
    {
        try {
            $annotations = $this->docParser->parse($input, $context);
        } catch (AnnotationException $e) {
            if (!preg_match('/The annotation "(.*)" .* was never imported/', $e->getMessage(), $matches)) {
                throw $e;
            }

            throw new \InvalidArgumentException(sprintf(
                'Unrecognized annotation %s, valid PHPBench annotations: @%s',
                $matches[1],
                implode(', @', array_keys($this->imports))
            ), null, $e);
        }

        return $annotations;
    }
}
