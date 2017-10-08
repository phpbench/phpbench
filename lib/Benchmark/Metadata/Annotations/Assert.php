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

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 * @Taget({"METHOD", "CLASS"})
 */
class Assert extends AbstractArrayAnnotation
{
    /**
     * @var array
     */
    private $config;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->config = $params;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
