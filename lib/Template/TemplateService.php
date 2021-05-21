<?php

namespace PhpBench\Template;

interface TemplateService
{
    public function get(string $serviceName): object;
}
