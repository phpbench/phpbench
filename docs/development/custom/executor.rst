Executor
========

An executor is responsible for executing your benchmarks. It accepts an
``ExecutionContext`` and returns ``ExecutionResults``.

PHPBench comes with two executor variations:

- The :ref:`executor_remote` executor executes you benchmarks in a separate
  process using a generated template.
- The :ref:`executor_local` executor executes you benchmarks the same process
  (sharing the runtime environment of PHPBench).

Example Executor
----------------

This executor will return a constant configured value for each iteration.

.. code-block:: php 

    <?php

    use PhpBench\Executor\BenchmarkExecutorInterface;
    use PhpBench\Executor\ExecutionContext;
    use PhpBench\Registry\Config;

    class AcmeExecutor implements BenchmarkExecutorInterface
    {
        private const CONFIG_MICROSECONDS = 'microseconds';

        /**
         * {@inheritDoc}
         */
        public function configure(OptionsResolver $options): void
        {
            $options->setDefaults([
                self::CONFIG_MICROSECONDS => 5
            ]);
        }

        public function execute(ExecutionContext $context, Config $config): ExecutionResults
        {
            return new ExecutionResults(
                new TimeResult($config[self::CONFIG_MICROSECONDS])
            );
        }

You can register it in your :doc:`extension <extension>` as follows:

.. code-block:: php

    <?php

    // ...
        public function load(Container $container): void
        {
            $container->register(MySymfonyCommand::class, function (Container $container) {
                return new AcmeExecutor();
            }, [
                CoreExtension::TAG_EXECUTOR => [
                    'name' => 'acme',
                ]
            ]);
        }

