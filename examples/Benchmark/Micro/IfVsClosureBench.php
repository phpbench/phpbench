<?php

namespace PhpBench\Examples\Benchmark\Micro;

/**
 * @BeforeMethods("setUp")
 */
class IfVsClosureBench
{
    public function setUp(): void
    {
        $this->number = rand(0, 10);
    }

    public function benchIf(): int
    {
        if ($this->number === 0) {
            return 0;
        }
        if ($this->number === 1) {
            return 1;
        }
        if ($this->number === 2) {
            return 2;
        }
        if ($this->number === 3) {
            return 3;
        }
        if ($this->number === 4) {
            return 4;
        }
        if ($this->number === 5) {
            return 5;
        }
        if ($this->number === 6) {
            return 6;
        }
        if ($this->number === 7) {
            return 7;
        }
        if ($this->number === 8) {
            return 8;
        }
        if ($this->number === 9) {
            return 9;
        }
        if ($this->number === 10) {
            return 10;
        }
    }
    public function benchClosure(): void
    {
        (function (int $number) {
            if ($number === 0) {
                return 0;
            }
            if ($number === 1) {
                return 1;
            }
            if ($number === 2) {
                return 2;
            }
            if ($number === 3) {
                return 3;
            }
            if ($number === 4) {
                return 4;
            }
            if ($number === 5) {
                return 5;
            }
            if ($number === 6) {
                return 6;
            }
            if ($number === 7) {
                return 7;
            }
            if ($number === 8) {
                return 8;
            }
            if ($number === 9) {
                return 9;
            }
            if ($number === 10) {
                return 10;
            }
        })($this->number);
    }

    public function benchDelegation(): int
    {
        return $this->delegate($this->number);
    }

    public function delegate(int $number): int
    {
        if ($number === 0) {
            return 0;
        }
        if ($number === 1) {
            return 1;
        }
        if ($number === 2) {
            return 2;
        }
        if ($number === 3) {
            return 3;
        }
        if ($number === 4) {
            return 4;
        }
        if ($number === 5) {
            return 5;
        }
        if ($number === 6) {
            return 6;
        }
        if ($number === 7) {
            return 7;
        }
        if ($number === 8) {
            return 8;
        }
        if ($number === 9) {
            return 9;
        }
        if ($number === 10) {
            return 10;
        }
    }
}
