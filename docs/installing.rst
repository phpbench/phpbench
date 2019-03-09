Installing
==========

PHPBench can be installed either as dependency for your project or as a global
package.

Install as a PHAR package
-------------------------

You can download PHPBench form the [Github
Releases](https://github.com/phpbench/phpbench/releases) or you can install it
with [Phive][https://github.com/phar-io/phive]

```bash
$ phive install phpbench
```

To upgrade PHPBench use the following command:

```bash
$ phive update phpbench
```

Composer Install
----------------

To install PHPBench as a dependency of your project:

.. code-block:: php

    $ composer require phpbench/phpbench @dev --dev


You may then run PHPBench from your project's directory as follows:

.. code-block:: bash

    $ ./vendor/bin/phpbench

Composer Global Install
-----------------------

You may install `PHPBench globally`_ using composer:

.. code-block:: php

    $ composer global require phpbench/phpbench @dev

.. note::

    You will need to add Composer's global ``bin`` directory to your systems
    ``PATH`` variable (on linux). See the above link.

You may now run PHPBench simply as:

.. code-block:: bash

    $ phpbench

.. _PHPBench globally: http://akrabat.com/global-installation-of-php-tools-with-composer/
.. _phar: https://phpbench.github.io/phpbench/phpbench.phar
.. _public key: https://phpbench.github.io/phpbench/phpbench.phar.pubkey
