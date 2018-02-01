<?php

namespace PhpBench\Model;

use InvalidArgumentException;

final class Tag
{
    /**
     * @var string
     */
    private $tag;

    public function __construct(string $tag)
    {
        if (!preg_match('{^[a-zA-Z_]+$}', $tag)) {
            throw new InvalidArgumentException(sprintf(
                'Tags can only contain alpha-numeric characters and _, got "%s"',
                $tag
            ));
        }
        $this->tag = $tag;
    }

    public function __toString()
    {
        return $this->tag;
    }
}
