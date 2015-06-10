<?php

namespace PhpBench\Console\Output;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This class enables the console output to be indented
 */
class IndentedConsoleOutput extends ConsoleOutput
{
    /**
     * @var integer
     */
    private $indentLevel = 0;

    private $cursorpos = 0;

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
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
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

        return parent::write($messages, $newline, $type);
    }
}
