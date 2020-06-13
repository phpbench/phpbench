<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Formatter;

/**
 * Formatter class which applies one or more registered classes to a subject value.
 */
class Formatter
{
    private $classes = [];
    private $formatRegistry;
    private $loader;

    public function __construct(FormatRegistry $registry, ClassLoader $loader = null)
    {
        $this->formatRegistry = $registry;
        $this->loader = $loader ?: new ClassLoader();
    }

    /**
     * Register classes from a given JSON encoded class definition file.
     *
     * @param string $filename
     */
    public function classesFromFile($filename)
    {
        $classes = $this->loader->load($filename);
        $this->registerClasses($classes);
    }

    /**
     * Register class definitions.
     *
     * Class definitions have the form $className => (array) $formatDefinitions
     *
     * @param array $classDefinitions
     */
    public function registerClasses(array $classDefinitions)
    {
        foreach ($classDefinitions as $className => $formatDefinitions) {
            $this->registerClass($className, $formatDefinitions);
        }
    }

    /**
     * Register a single class with its format definitions.
     *
     * Each format definition should be of the form:
     *
     *      [ $formatterName, { $options1 => $value 1 } ]
     *
     * i.e. a 2 element tuple with a scalar (the name of the formatter) and an
     * associative array of formatter options.
     *
     * @param string $name
     * @param array $formatDefinitions
     */
    public function registerClass($name, array $formatDefinitions)
    {
        $this->classes[$name] = $formatDefinitions;
    }

    /**
     * Apply the a set of classes (indicated by $classNames) to the given value.
     * Parameters can be given: parameters are used to replcae any tokens used in the format
     * options; this is required when options should be overridden on a per-subject basis.
     *
     * @param string[] $classNames
     * @param mixed $value
     * @param array $params
     *
     * @return string
     */
    public function applyClasses(array $classNames, $value, $params = [])
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Value is not a scalar, is a "%s"',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        foreach ($classNames as $class) {
            if (!isset($this->classes[$class])) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown class "%s", known classNames: "%s"',
                    $class, implode('", "', array_keys($this->classes))
                ));
            }

            $classDefinition = $this->classes[$class];

            try {
                foreach ($classDefinition as $formatDefinition) {
                    if (!isset($formatDefinition[1])) {
                        $formatDefinition[1] = [];
                    }
                    list($formatName, $formatConfig) = $formatDefinition;
                    $formatConfig = $this->substituteTokens($formatConfig, $params);

                    $format = $this->formatRegistry->get($formatName);
                    $defaultOptions = $format->getDefaultOptions();

                    $diff = array_diff(array_keys($formatConfig), array_keys($defaultOptions));

                    if ($diff) {
                        throw new \InvalidArgumentException(sprintf(
                            'Invalid options "%s" for format "%s", valid options: "%s"',
                            implode('", "', $diff), $formatName, implode('", "', array_keys($defaultOptions))
                        ));
                    }

                    $formatConfig = array_merge(
                        $defaultOptions,
                        $formatConfig
                    );

                    $value = $format->format($value, $formatConfig);
                }
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not load class definition: %s',
                    json_encode($classDefinition)
                ), 0, $e);
            }
        }

        return $value;
    }

    private function substituteTokens(array $formatConfig, $params)
    {
        foreach ($formatConfig as $key => $value) {
            if (is_array($value)) {
                $formatConfig[$key] = $this->substituteTokens($value, $params);

                continue;
            }

            preg_match_all('/{{(.*?)}}/', $value, $matches);
            $tokenNames = $matches[1];

            if (empty($tokenNames)) {
                continue;
            }

            $tokens = [];

            foreach ($tokenNames as $tokenName) {
                $realTokenName = trim($tokenName);

                // should we throw an exception or skip in this case? if we enforce formatter configs
                // on the generators then that is extra responsiblity for them.
                if (!isset($params[$realTokenName])) {
                    unset($formatConfig[$key]);

                    continue 2;
                }

                $tokens['{{' . $tokenName . '}}'] = $params[$realTokenName];
            }
            $formatConfig[$key] = strtr($value, $tokens);
        }

        return $formatConfig;
    }
}
