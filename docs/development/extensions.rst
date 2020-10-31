Extensions
==========

PHPBench allows you to create your own extensions.

Creating the Extension Class
----------------------------

First, create a dependency injection container extension:

.. code-block:: php

    <?php

    namespace Acme\PhpBench\MyExtension;

    use PhpBench\DependencyInjection\Container;
    use PhpBench\DependencyInjection\ExtensionInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class MyExtensionClass implements ExtensionInterface
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
            "Acme\\PhpBench\\MyExtension"
        ]
    }

Registering Services
--------------------

You can register new services. For example, you can register a new
command:

.. code-block:: php

    <?php

    namespace Acme\PhpBench\MyExtension;

    use PhpBench\DependencyInjection\Container;
    use PhpBench\DependencyInjection\ExtensionInterface;
    use PhpBench\Extension\CoreExtension;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class MyExtensionClass implements ExtensionInterface
    {
        public function configure(OptionsResolver $resolver): void
        {
        }

        public function load(Container $container): void
        {
            $container->register(MySymfonyCommand::class, function (Container $container) {
                return new MySymfonyCommand($container->get('some_service'));
            }, [
                CoreExtension::TAG_CONSOLE_COMMAND => []
            ]);
        }
    }

See the ``CoreExtension::TAG_*`` constants to see which extension points are
available.
