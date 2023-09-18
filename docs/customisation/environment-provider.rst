Environment Provider
====================

Environment providers are used to record information about the running environment,
they are called before the benchmark is run.

Create a new environment provider class similar to the following:

.. codeimport:: ../../examples/Extension/Environment/HomeProvider.php
  :language: php

And register with your DI container:

.. codeimport:: ../../examples/Extension/AcmeExtension.php
  :language: php
  :sections: all,env_provider_di

Run it with:

.. code-block:: bash

  $ phpbench run --report=env
