<?php

namespace PhpBench\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

/**
 * This class enables the console output to be indented
 */
class OutputIndentDecorator extends ConsoleOutput
{
    /**
     * @var integer
     */
    private $indentLevel = 0;

    /**
     * @var integer
     */
    private $cursorpos = 0;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Number of spaces to indent output with.
     *
     * @param integer
     */
    public function setIndentLevel($level)
    {
        $this->indentLevel = $level;
    }

    /**
     * {@inheritDoc}
     */
    public function write($messages, $newline = false, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $messages = (array) $messages;

        if ($newline) {
            $this->cursorpos = -1; 
        }

        if ($this->cursorpos <= 0) {
            foreach ($messages as &$message) {
                $message = str_repeat('  ', $this->indentLevel) . $message;
            }
        }

        $this->cursorpos++;

        return $this->output->write($messages, $newline, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function writeln($messages, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->write($messages, true, $type);
    }

    /**
     * {@inheritDoc}
     */
    public function setVerbosity($level)
    {
        $this->output->setVerbosity($level);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbosity()
    {
        return $this->output->getVerbosity();
    }

    /**
     * {@inheritDoc}
     */
    public function setDecorated($decorated)
    {
        $this->output->setDecorated($decorated);
    }

    /**
     * {@inheritDoc}
     */
    public function isDecorated()
    {
        return $this->output->isDecorated();
    }

    /**
     * {@inheritDoc}
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        $this->output->setFormatter($formatter);
    }

    /**
     * {@inheritDoc}
     */
    public function getFormatter()
    {
        return $this->output->getFormatter();
    }
}
