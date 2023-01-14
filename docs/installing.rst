Installing
==========

PHPBench can be installed either as dependency for your project or as a global
package.

Composer Install
----------------

To install PHPBench as a dependency of your project:

.. code-block:: bash

    $ composer require phpbench/phpbench --dev


You may then run PHPBench from your project's directory as follows:

.. code-block:: bash

    $ ./vendor/bin/phpbench

Install with PHIVE
------------------

Install with The PHAR Installation and Verification Environment `PHIVE <https://phar.io>`_:

.. code-block:: bash

    $ phive install phpbench

Install as a PHAR
-----------------

You can download `phpbench.phar` and the `phpbench.phar.asc`:
as follows:

.. code-block:: bash

    $ curl -Lo phpbench.phar https://github.com/phpbench/phpbench/releases/latest/download/phpbench.phar
    $ curl -Lo phpbench.phar.asc https://github.com/phpbench/phpbench/releases/latest/download/phpbench.phar.asc

The PHAR is signed. In order to verify that it was signed by the PHPBench team execute the
following:

.. code-block:: bash

     gpg --recv-keys 1EF396F668895578CAB457A26FC579F5F0FCC966
     gpg --with-fingerprint --verify phpbench.phar.asc phpbench.phar

You should then see something like the following:

.. code-block:: bash

    gpg: Signature made Tue 13 Apr 2021 16:35:57 BST
    gpg:                using RSA key 29BE1AD59988642ADCDFC86715E1F8E2B149E6F5
    gpg: Good signature from "Daniel Leech (PHPBench Github Key) <daniel@dantleech.com>" [ultimate]
    Primary key fingerprint: 29BE 1AD5 9988 642A DCDF  C867 15E1 F8E2 B149 E6F5
