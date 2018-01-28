<?php

namespace PhpBench\Storage;

interface UuidResolverInterface
{
    public function supports(string $reference): bool;

    public function resolve(string $reference): string;
}
