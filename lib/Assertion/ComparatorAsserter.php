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
    }

    public function assert(AssertionData $data, Config $config)
    {
        $comparator = $config[self::OPTION_COMPARATOR];
        $stat = $config[self::OPTION_STAT];
        $expectedValue = $config[self::OPTION_VALUE];
        $value = $data->getDistribution()[$stat];

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
            'Unknown comparator "%s"', $comparator
        ));
    }

    private function humanize(string $comparator)
    {
        return self::HUMANIZED[$comparator];
    }
}
