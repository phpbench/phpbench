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

namespace PhpBench\Extension;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsoleExtension implements ExtensionInterface
{
    public const PARAM_ANSI = 'console.ansi';
    public const PARAM_ERROR_STREAM = 'console.error_stream';
    public const PARAM_OUTPUT_STREAM = 'console.output_stream';
    public const PARAM_DISABLE_OUTPUT = 'console.disable_output';

    public const TAG_CONSOLE_COMMAND = 'console.command';

    public const SERVICE_OUTPUT_ERR = 'console.stream_err';
    public const SERVICE_OUTPUT_STD = 'console.stream_std';

    public function configure(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([

            self::PARAM_ANSI => null,
            self::PARAM_DISABLE_OUTPUT => false,
            self::PARAM_OUTPUT_STREAM => 'php://stdout',
            self::PARAM_ERROR_STREAM => 'php://stderr',
        ]);

        $resolver->setAllowedTypes(self::PARAM_ANSI, ['bool', 'null']);
        $resolver->setAllowedTypes(self::PARAM_DISABLE_OUTPUT, ['bool']);
        $resolver->setAllowedTypes(self::PARAM_ERROR_STREAM, ['string']);
        $resolver->setAllowedTypes(self::PARAM_OUTPUT_STREAM, ['string']);
        SymfonyOptionsResolverCompat::setInfos($resolver, [
            self::PARAM_ANSI => 'Enable or disable ANSI control characters (e.g. console colors)',
            self::PARAM_OUTPUT_STREAM => 'Change the normal output stream - the output stream used for reports',
            self::PARAM_ERROR_STREAM => 'Change the error output stream - the output stream used for diagnostics (e.g. progress loggers use this stream)',
            self::PARAM_DISABLE_OUTPUT => 'Disable output from both STDOUT and STDERR',
        ]);
    }

    public function load(Container $container): void
    {
        $container->register(self::SERVICE_OUTPUT_STD, function (Container $container) {
            return $this->createOutput($container, self::PARAM_OUTPUT_STREAM);
        });

        $container->register(self::SERVICE_OUTPUT_ERR, function (Container $container) {
            return $this->createOutput($container, self::PARAM_ERROR_STREAM);
        });

        $container->register(InputInterface::class, function (Container $container) {
            return new ArgvInput();
        });
    }

    private function createOutput(Container $container, string $type): OutputInterface
    {
        if ($container->getParameter(self::PARAM_DISABLE_OUTPUT)) {
            return new NullOutput();
        }

        $output = (function (string $name): OutputInterface {
            $resource = fopen($name, 'w');

            if (false === $resource) {
                throw new RuntimeException(sprintf(
                    'Could not open stream "%s"',
                    $name
                ));
            }

            return new StreamOutput($resource);
        })($container->getParameter($type));

        if (null !== $ansi = $container->getParameter(self::PARAM_ANSI)) {
            $output->setDecorated($ansi);
        }

        $output->getFormatter()->setStyle('success', new OutputFormatterStyle('black', 'green', []));
        $output->getFormatter()->setStyle('baseline', new OutputFormatterStyle('cyan', null, []));
        $output->getFormatter()->setStyle('result-neutral', new OutputFormatterStyle('cyan', null, []));
        $output->getFormatter()->setStyle('result-good', new OutputFormatterStyle('green', null, []));
        $output->getFormatter()->setStyle('result-none', new OutputFormatterStyle(null, null, []));
        $output->getFormatter()->setStyle('result-failure', new OutputFormatterStyle('white', 'red', []));
        $output->getFormatter()->setStyle('title', new OutputFormatterStyle('white', null, ['bold']));
        $output->getFormatter()->setStyle('subtitle', new OutputFormatterStyle('white', null, []));
        $output->getFormatter()->setStyle('description', new OutputFormatterStyle(null, null, []));

        return $output;
    }
}
