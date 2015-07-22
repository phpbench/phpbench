<?php

namespace PhpBench\Report\Tool;

/**
 * Handle formatting for display.
 *
 * The format() method accepts a value and an array of formats to apply to the
 * value.
 *
 * Formats are printf strings by default, but if prefixed with "!" one of the
 * defined formatting callbacks is used instead.
 */
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

    /**
     * Format the given value using the given format(s).
     * See the documentation for this class.
     *
     * @param mixed $value
     * @param string|array $formats
     * @return string
     */
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
