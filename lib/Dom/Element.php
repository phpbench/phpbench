<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Dom;

class Element extends \DOMElement
{
    public function appendElement($name, $value = null)
    {
        return $this->appendChild(new self($name, $value));
    }

    public function query($xpath)
    {
        return $this->ownerDocument->xpath()->query($xpath, $this);
    }

    public function evaluate($expression)
    {
        return $this->ownerDocument->xpath()->evaluate($expression, $this);
    }
}
