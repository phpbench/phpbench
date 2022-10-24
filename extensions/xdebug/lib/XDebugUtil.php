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

namespace PhpBench\Extensions\XDebug;

use PhpBench\Executor\ExecutionContext;
use RuntimeException;

class XDebugUtil
{
    public static function fromEnvironment(): XDebugUtil
    {
        $xdebugVersion = phpversion('xdebug');
        $xdebugUseCompression = ini_get('xdebug.use_compression') === '1';

        return new XDebugUtil($xdebugVersion, $xdebugUseCompression);
    }

    /**
     * @var string|false
     */
    private $xdebugVersion;
    /**
     * @var bool
     */
    private $xdebugUseCompression;

    /**
     * @param string|false     $xdebugVersion
     */
    public function __construct($xdebugVersion, bool $xdebugUseCompression)
    {
        $this->xdebugVersion = $xdebugVersion;
        $this->xdebugUseCompression = $xdebugUseCompression;
    }

    public function filenameFromContext(ExecutionContext $context, $extension = ''): string
    {
        $name = sprintf(
            '%s%s%s',
            $context->getClassName(),
            $context->getMethodName(),
            $context->getParameterSetName()
        );


        return md5($name) . $extension;
    }

    public function getCachegrindExtensionOfGeneratedFile(): string
    {
        $xdebugVersion = $this->discoverXdebugMajorVersion();

        if ($xdebugVersion === '3' && $this->xdebugUseCompression) {
            return '.cachegrind.gz';
        }

        return '.cachegrind';
    }

    public function discoverXdebugMajorVersion(): string
    {
        if ($this->xdebugVersion === false) {
            throw new RuntimeException(
                'Xdebug is not installed'
            );
        }

        return substr($this->xdebugVersion, 0, 1);
    }
}
