Installing
==========

PHPBench can be installed either as dependency for your project or as a global
package.

.. note::

    In the future there may be a PHAR version.

Composer Install
----------------

To install PHPBench as a dependency of your project:

.. code-block:: php

    $ composer require phpbench/phpbench @dev


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
