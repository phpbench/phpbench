<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Asserter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Math\Distribution;
use PhpBench\Registry\Config;
use PhpBench\Assertion\AssertionFailure;


class ComparatorAsserter implements Asserter
{
    const LESS_THAN = '<';
    const GREATER_THAN = '>';

    const OPTION_COMPARATOR = 'comparator';
    const HUMANIZED = [
        self::LESS_THAN => 'less than',
        self::GREATER_THAN => 'greater than',
    ];
    const OPTION_STAT = 'stat';
    const OPTION_VALUE = 'value';

    /**
     * {@inheritDoc}
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
    }

    public function assert(Distribution $distribution, Config $config)
    {
        $comparator = $config[self::OPTION_COMPARATOR];
        $stat = $config[self::OPTION_STAT];
        $expectedValue = $config[self::OPTION_VALUE];
        $value = $distribution[$stat];

        if (false === $this->compare($expectedValue, $value, $comparator)) {
            throw new AssertionFailure(sprintf(
                '%s is not %s %s, it was %s',
                $stat,
                $this->humanize($comparator),
                $expectedValue,
                $value
            ));
        }
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
            'Unknown comparator "%s"',$comparator
        ));
    }

    private function humanize(string $comparator)
    {
        return self::HUMANIZED[$comparator];
    }
}

