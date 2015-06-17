<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver as BaseOptionsResolver;

class OptionsResolver extends BaseOptionsResolver
{
    /**
     * Return true if this class is of the 2.6 version.
     *
     * @return bool
     */
    private function is26()
    {
        return method_exists($this, 'setDefault');
    }

    /**
     * @see OptionsResolver::setAllowedValues
     */
    public function setBCAllowedValues($option, $values = null)
    {
        if (false === $this->is26()) {
            return parent::setAllowedValues($option);
        }

        foreach ($option as $key => $values) {
            parent::setAllowedValues($key, $values);
        }
    }

    /**
     * @see OptionsResolver::setAllowedTypes
     */
    public function setBCAllowedTypes($option, $values = null)
    {
        if (false === $this->is26()) {
            return parent::setAllowedTypes($option);
        }

        foreach ($option as $key => $values) {
            parent::setAllowedTypes($key, $values);
        }
    }

    public function setBCNormalizers($normalizers)
    {
        if (false === $this->is26()) {
            return parent::setNormalizers($normalizers);
        }

        foreach ($normalizers as $key => $normalizer) {
            parent::setNormalizer($key, $normalizer);
        }
    }
}
