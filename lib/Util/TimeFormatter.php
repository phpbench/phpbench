<?php

namespace PhpBench\Util;

use PhpBench\Util\TimeUnit;

class TimeFormatter
{
    const MODE_THROUGHPUT = 'throughput';
    const MODE_TIME = 'time';

    private $timeUnit;
    private $mode;

    public function __construct(
        TimeUnit $timeUnit,
        $mode = self::MODE_TIME
    )
    {
        $this->timeUnit = $timeUnit;
        $this->mode = $mode;
    }

    public function convert($time, $mode = null, $unit = null)
    {
        $mode = $mode ?: $this->mode;
        $validModes = array(self::MODE_THROUGHPUT, self::MODE_TIME);

        if (!in_array($mode, $validModes)) {
            throw new \InvalidArgumentException(sprintf(
                'Time mode must be one of "%s", got "%s"',
                implode('", "', $validModes), $mode
            ));
        }

        switch ($mode) {
            case self::MODE_THROUGHPUT:
                return $this->timeUnit->intoDestUnit($time, $unit);
                break;
            case self::MODE_TIME:
                return $this->timeUnit->toDestUnit($time, $unit);
                break;
        }
    }

    public function format($time, $mode = null, $unit = null)
    {
        $value = number_format($this->convert($time, $mode, $unit), 3);
        $suffix = $this->getDestSuffix($mode, $unit);

        return $value . $suffix;
    }

    public function getDestSuffix($mode = null, $unit = null)
    {
        $mode = $mode ?: $this->mode;

        if (null === $unit || $this->timeUnit->isOverridden()) {
            $suffix = $this->timeUnit->getDestSuffix();
        } else {
            $suffix = TimeUnit::getSuffix($unit);
        }

        if ($mode === self::MODE_THROUGHPUT) {
            return sprintf('ops/%s', $suffix);
        }

        return $suffix;
    }
}
