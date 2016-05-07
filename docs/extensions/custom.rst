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

PHPBench as a project dependency
--------------------------------

If you are using PHPBench as a ``require-dev`` dependency of your project, and
the extension is in your projects autoloader, then you are done,
congratulations!

PHPBench as a PHAR
------------------

If you are using the PHAR version of PHPBench then you will need to tell
PHPBench where it can find an autoloader for your extension (or extensions):

.. code-block:: javascript

    {
        "extension_autoloader": "/home/daniel/www/phpbench/phpbench-example-extension/vendor/autoload.php"
    }

If you have multiple extensions you may consider creating an "extension
project" e.g.

.. code-block:: bash

    $ mkdir phpbench-extensions
    $ cd phpbench-extensions
    $ composer require vendor/my-phpbench-extension-1
    $ composer require vendor/my-phpbench-extension-2

and then using the ``autoload.php`` of this project.

.. _here: https://github.com/phpbench/phpbench-example-extension
.. _CoreExtension: https://github.com/phpbench/phpbench/blob/master/lib/Extension/CoreExtension.php
