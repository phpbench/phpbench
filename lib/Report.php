<?php

namespace PhpBench;

use PhpBench\Result\SuiteResult;

class Report
{
    private $dataProvider;
    private $formatters;

    final public function __construct()
    {
        $this->configure();
    }

    /**
     * @param ReportDataProvider $dataProvider
     */
    public function setDataProvider(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @param array $formatters
     */
    public function setFormatters(array $formatters)
    {
        $this->formatters = $formatters;
    }

    public function configure()
    {
    }

    /**
     * @param SuiteResult $result
     * @param array $formats
     */
    public function execute(SuiteResult $result, $formats = array())
    {
        $data = $this->dataProvider->provide($result);
        foreach ($formats as $format) {
            if (!isset($this->formatters[$format])) {
                throw new \InvalidArgumentException(sprintf(
                    'No formatter has been configured with key "%s". Known formatter keys: "%s"',
                    $format, implode('", "', array_keys($this->formatters))
                ));
            }

            $this->formatters->format($data);
        }
    }
}
