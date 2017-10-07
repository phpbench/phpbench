<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Assertion;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PhpBench\Math\Distribution;
use PhpBench\Registry\Config;
use PhpBench\Assertion\AssertionFailure;


class ComparatorAssertion implements Assertion
{
    const LESS_THAN = '<';
    const GREATER_THAN = '>';
    const APPROXIMATELY = '~=';
    const OPTION_COMPARATOR = 'comparator';
    const HUMANIZED = [
        self::LESS_THAN => 'less than',
        self::GREATER_THAN => 'greater than',
    ];

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options)
    {
        $options->setDefault(self::OPTION_COMPARATOR, self::LESS_THAN);
        $options->setAllowedValues(self::OPTION_COMPARATOR, [
            self::LESS_THAN,
            self::GREATER_THAN,
            self::APPROXIMATELY
        ]);
    }

    public function assert(string $property, $expectedValue, Distribution $distribution, Config $config)
    {
        $comparator = $config[self::OPTION_COMPARATOR];
        $value = $distribution[$property];

        if (false === $this->compare($expectedValue, $value, $comparator)) {
            throw new AssertionFailure(sprintf(
                '%s is not %s %s, it was %s',
                $property,
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

