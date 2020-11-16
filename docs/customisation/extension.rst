Extensions
==========

PHPBench allows you to create your own extensions, allowing you to register new:

- :doc:`Benchmark Executors <executor>`
- :doc:`Progress Loggers <progress-logger>`
- :doc:`Commands <command>`
- :doc:`Reports <report>`

Create a new extension package
------------------------------

.. note:: 

    This is optional if you do not wish to distribute your extension
    you can skip this step.

Create a new project, for example:

.. code-block:: bash

    $ composer create-project my-phpbench-extension

Include PHPBench as a dev-depenency:

.. code-block:: bash

    $ composer require phpbench/phpbench --dev

Create the Dependency Injection Extension
-----------------------------------------

First, create a dependency injection container extension:

.. code-block:: php

    <?php

    namespace PhpBench\Examples\Extension;

    use PhpBench\DependencyInjection\Container;
    use PhpBench\DependencyInjection\ExtensionInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class AcmeExtension implements ExtensionInterface
    {
        public function configure(OptionsResolver $resolver): void
        {
        }

        public function load(Container $container): void
        {
        }
    }

Then it can be registered in ``phpbench.json``:

.. code-block:: json

    {
        "extensions": [
            "PhpBench\\Examples\\Extension\\AcmeExtension"
        ]
    }

Registering Services
--------------------

You can register new services which will be integrated with PHPBench via
_tags_. 

For example, to register a new :doc:`command`:

.. code-block:: php

    <?php

    namespace PhpBench\Examples\Extension;

    use PhpBench\DependencyInjection\Container;
    use PhpBench\DependencyInjection\ExtensionInterface;
    use PhpBench\Examples\Extension\Command\CatsCommand;
    use PhpBench\Extension\CoreExtension;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class AcmeExtension implements ExtensionInterface
    {
        public function configure(OptionsResolver $resolver): void
        {
        }

        public function load(Container $container): void
        {
            $container->register(CatsCommand::class, function (Container $container) {
                return new CatsCommand(5);
            }, [
                CoreExtension::TAG_CONSOLE_COMMAND => []
            ]);
        }
    }

Configuration Parameters
------------------------

You can define configuration parameters via the Symfony OptionsResolver_ in the ``configure`` method:

.. code-block:: php

    <?php

    namespace PhpBench\Examples\Extension;

    use PhpBench\DependencyInjection\Container;
    use PhpBench\DependencyInjection\ExtensionInterface;
    use PhpBench\Examples\Extension\Command\CatsCommand;
    use PhpBench\Extension\CoreExtension;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class AcmeExtension implements ExtensionInterface
    {
        private const PARAM_NUMBER_OF_CATS = 'acme.number_of_cats';

        public function configure(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                self::PARAM_NUMBER_OF_CATS => 7
            ]);
        }

        // ...
    }

You can then use this parameter when creating your service using
``Container#getParameter``:

.. code-block:: php

    <?php

    // ...
        public function load(Container $container): void
        {
            $container->register(CatsCommand::class, function (Container $container) {
                return new CatsCommand($container->getParameter(self::PARAM_NUMBER_OF_CATS));
            }, [
                CoreExtension::TAG_CONSOLE_COMMAND => []
            ]);
        }

And it the value can be set in ``phpbench.json`` configuration


.. code-block:: json

    {
        "acme.number_of_cats": 8
    }

.. _OptionsResolver: https://symfony.com/doc/current/components/options_resolver.html
