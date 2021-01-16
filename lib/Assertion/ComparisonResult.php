<?php

namespace PhpBench\Assertion;

class ComparisonResult
{
    /**
     * @var bool
     */
    private $result;

    /**
     * @var bool
     */
    private $tolerated;

    private function __construct(bool $result, bool $tolerated)
    {
        $this->result = $result;
        $this->tolerated = $tolerated;
    }

    public function isTolerated(): bool
    {
        return $this->tolerated;
    }

    public function isTrue(): bool
    {
        return $this->result;
    }

    public static function tolerated(): self
    {
        return new self(false, true);
    }

    public static function false(): self
    {
        return new self(false, false);
    }

    public static function true(): self
    {
        return new self(true, false);
    }
}
