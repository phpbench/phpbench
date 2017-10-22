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

use PhpBench\Math\Distribution;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Util\TimeUnit;

class ComparatorAsserter implements Asserter
{
    const LESS_THAN = '<';
    const GREATER_THAN = '>';

    const OPTION_COMPARATOR = 'comparator';
    const OPTION_STAT = 'stat';
    const OPTION_VALUE = 'value';

    const HUMANIZED = [
        self::LESS_THAN => 'less than',
        self::GREATER_THAN => 'greater than',
    ];
    const OPTION_TIME_UNIT = 'time_unit';
    const OPTION_MODE = 'mode';

    /**
     * @var TimeUnit
     */
    private $timeUnit;

    public function __construct(TimeUnit $timeUnit)
    {
        $this->timeUnit = $timeUnit;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options)
    {
        $options->setDefault(self::OPTION_COMPARATOR, self::LESS_THAN);
        $options->setAllowedValues(self::OPTION_COMPARATOR, [
            self::LESS_THAN,
            self::GREATER_THAN,
        ]);

        $options->setRequired(self::OPTION_STAT);
        $options->setRequired(self::OPTION_VALUE);
        $options->setDefault(self::OPTION_TIME_UNIT, TimeUnit::MICROSECONDS);
        $options->setDefault(self::OPTION_MODE, TimeUnit::MODE_TIME);
        $options->setAllowedValues(self::OPTION_MODE, [
            TimeUnit::MODE_TIME,
            TimeUnit::MODE_THROUGHPUT,
        ]);
    }

    public function assert(AssertionData $data, Config $config)
    {
        $comparator = $config[self::OPTION_COMPARATOR];
        $stat = $config[self::OPTION_STAT];
        $expectedValue = $config[self::OPTION_VALUE];
        $mode = $config[self::OPTION_MODE];
        $timeUnit = $config[self::OPTION_TIME_UNIT];

        $value = $data->getDistribution()[$stat];

        switch ($mode) {
            case TimeUnit::MODE_THROUGHPUT:
                $this->assertThroughput($stat, $timeUnit, $comparator, $value, $expectedValue);
                return;
            case TimeUnit::MODE_TIME:
                $this->assertTime($stat, $timeUnit, $comparator, $value, $expectedValue);
                return;
        }

        throw new \RuntimeException(sprintf(
            'Invalid mode "%s"', $mode
        ));
    }

    private function convertExpectedValueToMicroseconds($value, $timeUnit)
    {
        return $this->timeUnit->convert($value, $timeUnit, TimeUnit::MICROSECONDS, TimeUnit::MODE_TIME);;
    }

    private function compare($expectedValue, $value, $comparator)
    {
        switch ($comparator) {
            case self::LESS_THAN:
                return $value < $expectedValue;
            case self::GREATER_THAN:
                return $value > $expectedValue;
        }

        throw new \RuntimeException(sprintf(
            'Unknown comparator "%s"', $comparator
        ));
    }

    private function humanize(string $comparator)
    {
        return self::HUMANIZED[$comparator];
    }

    private function assertThroughput(string $stat, string $timeUnit, string $comparator, $value, $expectedValue)
    {
        $value = TimeUnit::convertInto($value, TimeUnit::MICROSECONDS, $timeUnit);

        if (false === $this->compare($expectedValue, $value, $comparator)) {
            throw new AssertionFailure(sprintf(
                'Throughput for %s is not %s %s, it was %s',
                $stat,
                $this->humanize($comparator),
                $this->formatThroughput($expectedValue, $timeUnit),
                $this->formatThroughput($value, $timeUnit)
            ));
        }
    }

    private function assertTime(string $stat, $timeUnit, $comparator, $value, $expectedValue)
    {
        $expectedValue = $this->convertExpectedValueToMicroseconds($expectedValue, $timeUnit);

        if (false === $this->compare($expectedValue, $value, $comparator)) {
            throw new AssertionFailure(sprintf(
                '%s is not %s %s, it was %s',
                $stat,
                $this->humanize($comparator),
                $this->timeUnit->format($expectedValue, $timeUnit, TimeUnit::MODE_TIME),
                $this->timeUnit->format($value, $timeUnit, TimeUnit::MODE_TIME)
            ));
        }
    }

    private function formatThroughput($value, string $timeUnit)
    {
        return sprintf(
            '%s%s',
            number_format($value, $this->timeUnit->getPrecision()),
            $this->timeUnit->getSuffix($timeUnit, TimeUnit::MODE_THROUGHPUT)
        );
    }
}

