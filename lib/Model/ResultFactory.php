<?php

namespace PhpBench\Model;

interface ResultFactory
{
    /**
     * @param parameters $data 
     */
    public function create(array $data): ResultInterface;
}
