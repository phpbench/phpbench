<?php

namespace PhpBench\Benchmark;

class Teleflector
{
    private $telespector;

    public function __construct(
        Telespector $telespector
    )
    {
        $this->telespector = $telespector;
    }

    public function getClassInfo($file)
    {
        $classFqn = $this->getClassNameFromFile($file);

        $classInfo = $this->telespector->execute(__DIR__ . '/template/teleflector.template', array(
            'file' => $file,
            'class' => $classFqn
        ));

        return $classInfo;
    }

    public function getParameterSets($file, $paramProviders)
    {
        $parameterSets = $this->telespector->execute(__DIR__ . '/template/parameter_set_extractor.template', array(
            'file' => $file,
            'class' => $this->getClassNameFromFile($file),
            'paramProviders' => var_export($paramProviders, true),
        ));

        return $parameterSets;
    }

    /**
     * Return the class name from a file.
     *
     * Taken from http://stackoverflow.com/questions/7153000/get-class-name-from-file
     *
     * @param string $file
     *
     * @return string
     */
    private function getClassNameFromFile($file)
    {
        $fp = fopen($file, 'r');

        $class = $namespace = $buffer = '';
        $i = 0;

        while (!$class) {
            if (feof($fp)) {
                break;
            }

            $buffer .= fread($fp, 512);
            $tokens = @token_get_all($buffer);

            if (strpos($buffer, '{') === false) {
                continue;
            }

            for (;$i < count($tokens);$i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j = $i + 1;$j < count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace .= '\\' . $tokens[$j][1];
                        } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }

                if ($tokens[$i][0] === T_CLASS) {
                    for ($j = $i + 1;$j < count($tokens);$j++) {
                        if ($tokens[$j] === '{') {
                            $class = $tokens[$i + 2][1];
                        }
                    }
                }
            }
        };

        if (!$class) {
            return;
        }

        return $namespace . '\\' . $class;
    }
}
