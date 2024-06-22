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

namespace PhpBench\Assertion;

class AssertionResult
{
    final public const TOLERATED = 'tolerated';
    final public const FAIL = 'fail';
    final public const OK = 'ok';

    public function __construct(private readonly string $type, private readonly ?string $message = null)
    {
    }

    public static function tolerated(string $message = ''): self
    {
        return new self(self::TOLERATED, $message);
    }

    public static function fail(string $message = ''): self
    {
        return new self(self::FAIL, $message);
    }

    public static function ok(): self
    {
        return new self(self::OK);
    }

    public function isTolerated(): bool
    {
        return $this->type === self::TOLERATED;
    }

    public function isFail(): bool
    {
        return $this->type === self::FAIL;
    }

    public function getMessage(): string
    {
        return $this->message ?? '<no message>';
    }

    public function type(): string
    {
        return $this->type;
    }
}
