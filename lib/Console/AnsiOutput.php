<?php

namespace PhpBench\Console;

use Symfony\Component\Console\Output\OutputInterface;

final class AnsiOutput
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function writeRaw(string $message): void
    {
        $this->output->write($message, false, OutputInterface::OUTPUT_RAW);
    }

    public function write(string $message): void
    {
        $this->output->write($message);
    }

    public function writeln(string $message): void
    {
        $this->output->writeln($message);
    }

    public function clearLine(): void
    {
        $this->writeRaw("\x1B[2K");
    }

    public function clearRemaining(): void
    {
        $this->writeRaw("\x1B[0J");
    }

    public function moveDown(int $rows): void
    {
        $this->writeRaw("\x1B[" . $rows . 'B');
    }

}
