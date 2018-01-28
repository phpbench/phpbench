<?php

namespace PhpBench\Storage\UuidResolver;

use PhpBench\Storage\UuidResolverInterface;

class ChainResolver implements UuidResolverInterface
{
    /**
     * @var array
     */
    private $resolvers = [];

    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function supports(string $reference): bool
    {
        return true;
    }

    public function resolve(string $reference): string
    {
        /** @var UuidResolverInterface $resolver */
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($reference)) {
                return $resolver->resolve($reference);
            }
        }

        return $reference;
    }
}
