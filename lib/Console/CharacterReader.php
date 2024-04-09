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

namespace PhpBench\Console;

class CharacterReader
{
    private static bool $initialized = false;

    /**
     * If readline is installed, then prevent the user having to
     * press <return> in order to paginate.
     */
    private static function initialize(): void
    {
        // we could use extension_loaded but HHVM returns true and
        // still doesn't have this function...
        if (self::$initialized === false && function_exists('readline_callback_handler_install')) {
            readline_callback_handler_install('', function (): void {
            });
            self::$initialized = true;
        }
    }

    /**
     * Wait for a single character input and return it.
     *
     * @return ?string
     */
    public function read(): ?string
    {
        self::initialize();

        while (false !== $character = fgetc(STDIN)) {
            return $character;
        }

        return null;
    }
}
