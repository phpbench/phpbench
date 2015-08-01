<?php

namespace PhpBench\Report\Tool;

class Assert
{
    /**
     * Assert that an array has only the given keys.
     *
     * @param array $validKeys
     */
    public static function hasOnlyKeys(array $validKeys, array $array, $context)
    {
        $invalidKeys = array();
        foreach (array_keys($array) as $key) {
            if (!in_array($key, $validKeys)) {
                $invalidKeys[] = $key;
            }
        }

        if (!$invalidKeys) {
            return;
        }

        throw new \InvalidArgumentException(sprintf(
            'Invalid keys for %s: "%s". Valid keys are: "%s"',
            $context, implode('", "', $invalidKeys), implode('", "', $validKeys)
        ));
    }
}
