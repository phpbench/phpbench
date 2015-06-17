<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Generator;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use DTL\Cellular\Workspace;
use PhpBench\Console\Output\OutputIndentDecorator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Console\Helper\TableHelper;

class ConsoleTableReportGenerator extends BaseTabularReportGenerator
{
    public function configure(OptionsResolver $options)
    {
        parent::configure($options);
        $options->setDefaults(array(
            'style' => 'horizontal',
            'subject_meta' => true,
        ));
        $options->setBCAllowedValues(array(
            'style' => array('vertical', 'horizontal'),
        ));
    }

    public function doGenerate(Workspace $workspace, OutputInterface $output, array $options)
    {
        if (count($workspace) === 0) {
            return;
        }

        $output = $output;
        $output->getFormatter()->setStyle(
            'total', new OutputFormatterStyle(null, null, array())
        );
        $output->getFormatter()->setStyle(
            'blue', new OutputFormatterStyle('blue', null, array())
        );
        $output->getFormatter()->setStyle(
            'dark', new OutputFormatterStyle('black', null, array('bold'))
        );
        $output->getFormatter()->setStyle(
            'headline', new OutputFormatterStyle('white', 'blue', array('bold'))
        );

        if ($title = $workspace->getAttribute('title')) {
            $title = '  ' . $title . '  ';
            $output->writeln('<headline>' . str_repeat(' ', strlen($title)) . '</headline>');
            $output->writeln('<headline>' . $title . '</headline>');
            $output->writeln('<headline>' . str_repeat(' ', strlen($title)) . '</headline>');
            $this->setIndent($output, 1);
            $output->writeln('');

            if ($description = $workspace->getAttribute('description')) {
                $this->setIndent($output, 0);
                $output->writeln($description);
                $this->setIndent($output, 1);
                $output->writeln('');
            }
        }

        foreach ($workspace->getTables() as $data) {
            $this->setIndent($output, 1);

            if ($options['subject_meta']) {
                $line = array();
                if ($data->hasAttribute('class')) {
                    $line[] = sprintf(
                        '[%s->%s]',
                        $data->getAttribute('class'),
                        $data->getAttribute('subject')
                    );
                }

                if ($data->hasAttribute('groups') && $data->getAttribute('groups')) {
                    $line[] = sprintf(
                        '<blue>[%s]</blue>',
                        implode(', ', $data->getAttribute('groups'))
                    );
                }

                if ($line) {
                    $output->writeln(implode(' ', $line));
                }

                $description = $data->hasAttribute('description') ? $data->getAttribute('description') : false;

                if ($description) {
                    $output->writeln($description);
                }
            }

            $this->renderData($output, $data, $options);

            $output->writeln('');
        }

        $this->setIndent($output, 0);
    }

    private function renderData(OutputInterface $output, $data, $options)
    {
        // assign prevision to locally scoped variable for use in closures
        // in versions of PHP < 5.4
        $precision = $this->precision;

        $data->mapValues(function ($cell) {
            return null !== $cell->getValue() ? number_format($cell->getValue(), 2) : '∞';
        }, array('rps'));

        switch ($options['time_format']) {
            case 'integer':
                // format the float cells
                $data->mapValues(function ($cell) {
                    $value = $cell->getValue();
                    $value =  number_format($value);

                    return $value . '<dark>μs</dark>';
                }, array('.time'));
                break;
            default:
                // format the float cells
                $data->mapValues(function ($cell) use ($precision) {
                    $value = $cell->getValue();
                    $value =  number_format($value / 1000000, $precision);
                    $value = preg_replace('{^([0|\\.]+)(.+)$}', '<blue>\1</blue>\2', $value);

                    return $value . '<dark>s</dark>';
                }, array('.time'));
        }

        $data->mapValues(function ($cell) {
            $value = $cell->getValue();
            $groups = $cell->getGroups();
            $prefix = '';
            if (in_array('diff', $groups)) {
                if ($value > 0) {
                    $prefix = '+';
                }

                if ($value < 0) {
                    $prefix = '-';
                }
            }

            return $prefix . number_format($cell->getValue()) . '<dark>b</dark>';
        }, array('.memory'));

        // format the deviation
        $data->mapValues(function ($cell) {
            $prefix = '';
            if ($cell->getValue() > 0) {
                $prefix = '+';
            }

            return sprintf('%s%s', $prefix, number_format($cell->getValue(), 2)) . '%';
        }, array('.deviation'));

        $data->mapValues(function ($cell) {
            return number_format($cell->getValue(), 2) . '%';
        }, array('.variance'));

        // format the revolutions
        $data->mapValues(function ($cell) {
            return $cell->getValue() . '<dark>rps</dark>';
        }, array('.rps'));

        // format the footer
        $data->mapValues(function ($cell) {
            return sprintf('<total>%s</total>', $cell->getValue());
        }, array('.footer'));

        // handle null
        $data->mapValues(function ($cell) {
            if ($cell->getValue() === null) {
                return '-';
            }

            return $cell->getValue();
        });

        foreach ($data->getRows(array('spacer')) as $spacer) {
            $spacer->fill('--');
        }

        foreach ($data->getRows() as $row) {
            $row->mapValues(function ($cell) {
                $value = $cell->getValue();

                if (is_scalar($value)) {
                    return $value;
                }

                if (is_object($value)) {
                    if (method_exists($value, '__toString')) {
                        return $value->__toString();
                    }

                    return 'obj:' . spl_object_hash($value);
                }

                return json_encode($value);
            });
        }

        if ($options['style'] === 'horizontal') {
            $table = $this->createTable($output);
            $table->setHeaders($data->getColumnNames());
            foreach ($data->getRows() as $row) {
                $table->addRow($row->toArray());
            }

            $this->renderTable($table, $output);
        }

        if ($options['style'] === 'vertical') {
            foreach ($data->getRows() as $index => $row) {
                $output->writeln(sprintf('<comment>Row %d</comment>', $index));
                $table = $this->createTable($output);
                $table->setHeaders(array('column', 'value'));
                foreach ($row as $name => $cell) {
                    $table->addRow(array($name, $cell->getValue()));
                }
                $this->renderTable($table, $output);
            }
        }
    }

    private function setIndent(OutputInterface $output, $level)
    {
        if ($output instanceof OutputIndentDecorator) {
            $output->setIndentLevel($level);
        }
    }

    private function createTable(OutputInterface $output)
    {
        if (class_exists('Symfony\Component\Console\Helper\Table')) {
            return new Table($output);
        }

        return new TableHelper();
    }

    private function renderTable($table, OutputInterface $output)
    {
        if (class_exists('Symfony\Component\Console\Helper\Table')) {
            $table->render();

            return;
        }

        $table->render($output);
    }
}
