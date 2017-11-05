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

use PhpBench\Registry\Config;
use PhpBench\Util\TimeUnit;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComparatorAsserter implements Asserter
{
    const LESS_THAN = '<';
    const GREATER_THAN = '>';

    const OPTION_COMPARATOR = 'comparator';
    const OPTION_MODE = 'mode';
    const OPTION_STAT = 'stat';
    const OPTION_TIME_UNIT = 'time_unit';
    const OPTION_TOLERANCE = 'tolerance';
    const OPTION_VALUE = 'value';

    const HUMANIZED = [
        self::LESS_THAN => 'less than',
        self::GREATER_THAN => 'greater than',
    ];

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

        $options->setRequired(self::OPTION_VALUE);
        $options->setDefault(self::OPTION_TIME_UNIT, TimeUnit::MICROSECONDS);
        $options->setDefault(self::OPTION_MODE, TimeUnit::MODE_TIME);
        $options->setDefault(self::OPTION_TOLERANCE, 0);
        $options->setDefault(self::OPTION_STAT, 'mean');
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
        $tolerance = $config[self::OPTION_TOLERANCE];

        $value = $data->getDistribution()[$stat];

        switch ($mode) {
            case TimeUnit::MODE_THROUGHPUT:
                $this->assertThroughput($stat, $timeUnit, $comparator, $value, $expectedValue, $tolerance);

                return;
            case TimeUnit::MODE_TIME:
                $this->assertTime($stat, $timeUnit, $comparator, $value, $expectedValue, $tolerance);

                return;
        }

        throw new \RuntimeException(sprintf(
            'Invalid mode "%s"', $mode
        ));
    }

    private function assertThroughput(string $statName, string $timeUnit, string $comparator, $value, $expectedValue, $tolerance)
    {
        $value = TimeUnit::convertInto($value, TimeUnit::MICROSECONDS, $timeUnit);
        $this->check(
            'Throughput for %s is not %s %s, it was %s',
            $value,
            $expectedValue,
            $statName,
            TimeUnit::MODE_THROUGHPUT,
            $timeUnit,
            $comparator,
            $tolerance
        );
    }

    private function assertTime(string $statName, $timeUnit, $comparator, $value, $expectedValue, $tolerance)
    {
        $expectedValue = $this->convertValueToMicroseconds($expectedValue, $timeUnit);
        $tolerance = $this->convertValueToMicroseconds($tolerance, $timeUnit);

        $this->check(
            '%s is not %s %s, it was %s',
            $value,
            $expectedValue,
            $statName,
            TimeUnit::MODE_TIME,
            $timeUnit,
            $comparator,
            $tolerance
        );
    }

    private function check(string $failureMessage, $value, $expectedValue, string $statName, string $mode, string $timeUnit, string $comparator, $tolerance)
    {
        if (true === $this->compare($expectedValue, $value, $comparator)) {
            return;
        }

        $assertionClass = AssertionFailure::class;
        $lowerLimit = $expectedValue - $tolerance;
        $upperLimit = $expectedValue + $tolerance;

        if (
            $this->compare($lowerLimit, $value, $comparator) ||
            $this->compare($upperLimit, $value, $comparator)
        ) {
            $assertionClass = AssertionWarning::class;
        }

        throw new $assertionClass(sprintf(
            $failureMessage,
            $statName,
            $this->humanize($comparator),
            $this->formatValue($expectedValue, $timeUnit, $mode),
            $this->formatValue($value, $timeUnit, $mode)
        ));
    }

    private function formatValue($value, $timeUnit, $mode)
    {
        switch ($mode) {
            case TimeUnit::MODE_THROUGHPUT:
                return $this->formatThroughput($value, $timeUnit);
            case TimeUnit::MODE_TIME:
                return $this->timeUnit->format($value, $timeUnit, TimeUnit::MODE_TIME);
        }

        throw new \RuntimeException(sprintf(
            'Unknown time unit "%s"', $timeUnit
        ));
    }

    private function humanize(string $comparator)
    {
        return self::HUMANIZED[$comparator];
    }

    private function formatThroughput($value, string $timeUnit)
    {
        return sprintf(
            '%s%s',
            number_format($value, $this->timeUnit->getPrecision()),
            $this->timeUnit->getSuffix($timeUnit, TimeUnit::MODE_THROUGHPUT)
        );
    }

    private function convertValueToMicroseconds($value, $timeUnit)
    {
        return $this->timeUnit->convert($value, $timeUnit, TimeUnit::MICROSECONDS, TimeUnit::MODE_TIME);
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
}
