<?php

namespace PhpBench\Storage;

final class RefResolver
{
    /**
     * @var RefResolverInterface
     */
    private $innerResolver;

    public function __construct(RefResolverInterface $innerResolver)
    {
        $this->innerResolver = $innerResolver;
    }

    public function resolve(string $ref): string
    {
        return $this->innerResolver->resolve($ref) ?? $ref;
    }
}
