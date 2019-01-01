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

namespace PhpBench\Formatter\Format;

use PhpBench\Formatter\FormatInterface;

class TruncateFormat implements FormatInterface
{
    public function format($value, array $options)
    {
        if (strlen($value) <= $options['length']) {
            return $value;
        }

        $truncateLength = $options['length'] - strlen($options['pad']);

        switch ($options['position']) {
            case 'left':
                $string = $options['pad'] . substr($value, -$truncateLength);

                break;
            case 'right':
                $string = substr($value, 0, $truncateLength) . $options['pad'];

                break;
            case 'middle':
                $offset = (int) floor($truncateLength / 2);
                $left = substr($value, 0, $offset);
                $string = $left . $options['pad'];
                $string = $string . substr($value, -($options['length'] - strlen($string)));

                break;

            default:
                throw new \Exception(sprintf(
                    'Truncation position must be one of "%s", got "%s"',
                    implode('", "', ['left', 'right']), $options['position']
                ));
        }

        return $string;
    }

    public function getDefaultOptions()
    {
        return [
            'length' => 50,
            'position' => 'left',
            'pad' => '...',
        ];
    }
}
