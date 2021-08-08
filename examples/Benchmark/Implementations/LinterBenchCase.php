<?php

namespace PhpBench\Examples\Benchmark\Implementations;

// section: all
use Generator;
use PhpBench\Config\ConfigLinter;


/**
 * @Groups("implementations")
 */
abstract class LinterBenchCase
{
    /**
     * @var ?ConfigLinter
     */
    protected $linter;

    /**
     * @var ?string
     */
    protected $json;

    public function __construct()
    {
        $this->linter = $this->createLinter();
    }

    public abstract function createLinter(): ConfigLinter;

    /**
     * @ParamProviders({"provideScale"})
     * @BeforeMethods({"setUpJsonString"})
     */
    public function benchLint(): void
    {
        $this->linter->lint('path/to.json', $this->json);
    }

    public function setUpJsonString(array $params): void
    {
        $data = self::buildData($params[0]);
        $this->json = json_encode($data, JSON_PRETTY_PRINT);
    }

    public function provideScale(): Generator
    {
        yield [1];
        yield [2];
        yield [3];
    }

    public static function buildData(int $size): array
    {
        $data = [];
        for ($i = 0; $i < $size; $i++) {
            if ($size - 1 === 0) {
                $data['key-' . $i] = 'test';
                break;
            }
            $data['key-' . $i] = self::build($size - 1);
        }

        return $data;
    }
}
// endsection: all
