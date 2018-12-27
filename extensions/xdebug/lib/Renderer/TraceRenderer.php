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

namespace PhpBench\Extensions\XDebug\Renderer;

use PhpBench\Extensions\XDebug\Result\XDebugTraceResult;
use PhpBench\Formatter\Format\TimeUnitFormat;
use PhpBench\Formatter\Format\TruncateFormat;
use PhpBench\Model\Suite;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class TraceRenderer
{
    public function __construct()
    {
        $this->truncate = new TruncateFormat();
        $this->timeUnit = new TimeUnitFormat();
    }

    public function render(Suite $suite, OutputInterface $output, $options = [])
    {
        $options = array_merge([
            'filter_benchmark' => true,
            'show_args' => false,
            'filter' => null,
        ], $options);

        foreach ($suite->getIterations() as $iteration) {
            $this->renderIteration($iteration, $output, $options);
        }
    }

    private function renderIteration($iteration, $output, array $options)
    {
        $table = new Table($output);

        $table->setHeaders([
            '#', 'Level', 'Mem', 'Time', 'Time inc.', 'Time inc. %', 'Function', 'File',
        ]);

        $result = $iteration->getResult(XDebugTraceResult::class);
        $trace = $result->getTraceDocument();

        $subject = $iteration->getVariant()->getSubject();
        $benchmark = $subject->getBenchmark();

        if ($options['filter_benchmark']) {
            $selector = '//entry[@function="' . substr($benchmark->getClass(), 1) . '->' . $subject->getName() . '"]';
            $trace = $trace->queryOne($selector);
        }

        $this->renderEntries($trace, $table, $options);

        $table->render();
    }

    private function renderEntries(\DOMNode $trace, $table, array $options, $padding = 0)
    {
        $totalTime = $trace->evaluate('number(/trace/entry[1]/@end-time) - number(/trace/entry[1]/@start-time)') * 1E6;

        foreach ($trace->query('./entry') as $entryEl) {
            if (null === $options['filter'] || preg_match('{' . $options['filter'] .'}', $entryEl->getAttribute('function'))) {
                $timeInc = ($entryEl->getAttribute('end-time') - $entryEl->getAttribute('start-time')) * 1E6;

                $table->addRow([
                    $entryEl->getAttribute('func_nb'),
                    $entryEl->getAttribute('level'),
                    number_format($entryEl->getAttribute('start-memory')) . 'b',
                    $entryEl->getAttribute('start-time') . 's',

                    $this->timeUnit->format(
                        $timeInc,
                        array_merge($this->timeUnit->getDefaultOptions(), ['precision' => 0])
                    ),

                    number_format($timeInc / $totalTime * 100, 2),

                    $this->renderFunction($entryEl, $padding, $options),
                    sprintf(
                        '%s:%s',
                        str_replace(getcwd(), '.', $entryEl->getAttribute('filename')),
                        $entryEl->getAttribute('line')
                    ),
                ]);
            }

            $this->renderEntries($entryEl, $table, $options, $padding + 1);
        }
    }

    private function renderFunction(\DOMNode $entryEl, $padding, array $options)
    {
        $function = $entryEl->getAttribute('function');
        $args = [];

        foreach ($entryEl->query('./arg') as $argEl) {
            $argString = $this->truncate->format(
                $argEl->nodeValue,
                array_merge(
                    $this->truncate->getDefaultOptions(),
                    [
                        'length' => 100,
                    ]
                )
            );
            $args[] = str_replace("\n", '', $argString);
        }

        if ($options['show_args'] && $args) {
            return sprintf(
                '%s%s(%s',
                $pad = str_repeat(' ', $padding),
                $function,
                "\n" . $pad . ' ' . implode("\n" . $pad . ' ', $args) . "\n" . $pad . '</>)'
            );
        }

        return sprintf(
            '%s%s()',
            $pad = str_repeat(' ', $padding),
            $function
        );
    }
}
