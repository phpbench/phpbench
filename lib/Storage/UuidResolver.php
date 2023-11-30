<?php

namespace PhpBench\Storage;

/**
 * Wrapper class for the UUID resolver interface which will always return the
 * UUID
 */
final class UuidResolver
{
    public function __construct(private readonly UuidResolverInterface $innerResolver)
    {
    }

    public function resolve(string $ref): string
    {
        return $this->innerResolver->resolve($ref) ?? $ref;
    }
}
