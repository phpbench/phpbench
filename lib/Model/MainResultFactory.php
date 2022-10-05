<?php

namespace PhpBench\Model;

use PhpBench\Model\Result\Exception\UnkownResultType;

final class MainResultFactory
{
    /**
     * @var array<string,ResultFactory>
     */
    private $factories;

    /**
     * @param array<string,ResultFactory> $factories
     */
    public function __construct(array $factories)
    {
        $this->factories = $factories;
    }

    /**
     * @param parameters $data
     */
    public function create(string $type, array $data): ResultInterface
    {
        if (!array_key_exists($type, $this->factories)) {
            throw new UnkownResultType(sprintf(
                'Result type "%s" not known, known result types: "%s"',
                $type,
                implode('", "', array_keys($this->factories))
            ));
        }

        return $this->factories[$type]->create($data);
    }
}
