Custom Extensions
=================

.. note::

    Check out the example extension here_.

Writing custom extensions is quite easy, it is necessary to create a container
extension and then register that extension in your configuration:

.. code-block:: php

    <?php

    namespace Acme\PhpBench\Extension\Example;

    use PhpBench\DependencyInjection\ExtensionInterface;
    use PhpBench\DependencyInjection\Container;
    use Acme\PhpBench\Extension\Example\Command\FooCommand;
    use Acme\PhpBench\Extension\Example\Progress\FooLogger;

    class ExampleExtension implements ExtensionInterface
    {
        public function getDefaultConfig()
        {
            // default configuration for this extension
            return [
                'acme.example.message' => 'Hello World',
                'acme.progress.character' => 'x'
            ];
        }

        public function load(Container $container)
        {
            // register a command
            $container->register('acme.example.foo', function (Container $container) {
                return new FooCommand(
                    $container->getParameter('acme.example.message')
                );
            }, ['console.command' => []]);

            // register a progress logger
            $container->register('acme.example.progress_logger', function (Container $container) {
                return new FooLogger($container->get('benchmark.time_unit'));
            }, ['progress_logger' => ['name' => 'foo']]);

        }

        // called after load() can be used to add tagged services to existing services.
        public function build(Container $container)
        {
        }
    }

.. note::

    The third argument of the ``register`` method, this is a list of
    **tags**. Tags tell PHPUnit what these services are and how to use them.
    Checkout the CoreExtension_ to investigate all of the available tags.

and activate the extension in your ``phpbench.json`` file:

.. code-block:: javascript

    {
        "extensions": [
            "Acme\\PhpBench\\Extension\\Example\\ExampleExtension"
        ]
    }
