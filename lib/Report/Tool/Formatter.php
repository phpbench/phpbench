<?php

namespace PhpBench\Report\Tool;

class Formatter
{
    private $formatters = array();

    public function __construct()
    {
        $this->formatters = array(
            'number' => function ($value) {
                return number_format($value);
            },
            'balance' => function ($value) {
                if ($value > 0) {
                    $value = '+' . $value;
                }

                return $value;
            },
        );
    }

    public function format($value, $formats)
    {
        $formats = (array) $formats;

        foreach ($formats as $format) {
            if (substr($format, 0, 1) !== '!') {
                $value = sprintf($format, $value);
                continue;
            }

            $format = substr($format, 1);

            if (!isset($this->formatters[$format])) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown formatter "%s", known formatters: "%s"',
                    $format, implode('", "', array_keys($this->formatters))
                ));
            }

            $value = $this->formatters[$format]($value);
        }

        return $value;
    }
}
