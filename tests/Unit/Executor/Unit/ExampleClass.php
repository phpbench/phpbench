<?php

namespace PhpBench\Tests\Unit\Executor\Unit;

class ExampleClass
{
    public function register(?array $params): void {
        $res = fopen(__DIR__ . '/../../../Workspace/example.bench', 'a');
        fwrite($res, json_encode($params)."\n");
        fclose($res);
    }
}
