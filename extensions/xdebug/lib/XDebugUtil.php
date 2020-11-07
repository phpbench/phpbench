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

class XDebugUtil
{
    public static function filenameFromContext(ExecutionContext $context, $extension = ''): string
    {
        $name = sprintf(
            '%s%s%s',
            $context->getClassName(),
            $context->getMethodName(),
            $context->getParameterSetName()
        );


        return md5($name) . $extension;
    }
}
