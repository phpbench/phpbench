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
    public const TOLERATED = 'tolerated';
    public const FAIL = 'fail';
    public const OK = 'ok';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $message;

    public function __construct(string $type, ?string $message = null)
    {
        $this->type = $type;
        $this->message = $message;
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
