<?php

namespace PhpBench\Runner\Stage;

use Generator;
use PhpBench\Runner\Stage;

class AggregateStage implements Stage
{
    /**
     * @var Stage[]
     */
    private $stages;

    public function __construct(array $stages)
    {
        $this->stages = $stages;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $data = null;

        while (true) {
            /** @var Generator $stage */
            foreach ($this->stages as $stage) {
                $data = $stage->send($data);

                if (!$data instanceof Data) {
                    throw new RuntimeException(sprintf(
                        'All Stages must yield Data objects, got: "%s"',
                        is_object($data) ? get_class($data) : gettype($data)
                    ));
                }

                if (false === $stage->valid()) {
                    yield $data;
                    break 2;
                }
            }

            yield $data;
        }
    }
}
