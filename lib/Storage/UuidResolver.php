<?php

namespace PhpBench\Storage;

/**
 * Wrapper class for the UUID resolver interface which will always return the
 * UUID
 */
final class UuidResolver
{
    /**
     * @var UuidResolverInterface
     */
    private $innerResolver;

    public function __construct(UuidResolverInterface $innerResolver)
    {
        $this->innerResolver = $innerResolver;
    }

    public function resolve(string $ref): string
    {
        return $this->innerResolver->resolve($ref) ?? $ref;
    }
}
