Rector
======

The rector extension for refactoring PHPBench annotations to attributes.

Configuration
-------------

Add the following to your rector configuration file:

.. code-block:: php

    use Rector\Config\RectorConfig;

    return RectorConfig::configure()
        ->withSets([
            PhpBench\Extensions\Rector\Set\PhpBenchSetList::ANNOTATIONS_TO_ATTRIBUTES, // Added rector set
        ]);

Example
-------

.. code-block:: diff

    -/**
    - * @BeforeMethods({"setUp", "init"})
    - *
    - * @Revs(100)
    - */
    +use PhpBench\Attributes\BeforeMethods;
    +use PhpBench\Attributes\Revs;
    +
    +#[BeforeMethods(['setUp', 'init'])]
    +#[Revs(100)]
     final class ExampleBench
     {
    -    /**
    -     * @Revs(1000)
    -     */
    +    #[Revs(1000)]
         public function benchExample(): void
         {
             // ...
         }
     }
