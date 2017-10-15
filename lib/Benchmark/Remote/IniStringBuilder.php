<?php

namespace PhpBench\Benchmark\Remote;

class IniStringBuilder
{
    public function build(array $config)
    {
        $string = [];
        foreach ($config as $key => $values) {
            $values = (array) $values;

            foreach ($values as $value) {
                $string[] = sprintf('-d%s=%s', $key, $value);
            }
        }

        return implode(' ', $string);
    }
}
