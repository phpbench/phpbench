Reports
=======

Report Generator
----------------

The report generators are responsible for generating an object structure which
can be rendered to a result.

Reports consist of tables, which consist of rows, which in turn consist of
cells - which are infact :ref:`Expression Language <expression_language>` Nodes.

Example Generator
-----------------

.. codeimport:: ../../examples/Extension/Report/AcmeGenerator.php
  :language: php

And register with your DI container:

.. codeimport:: ../../examples/Extension/AcmeExtension.php
  :language: php
  :sections: all,report_generator_di

Use your new generator:

.. code-block:: bash

    $ phpbench run --report=catordog

    Cats report
    ===========

    Are cats really cats or are they dogs?

    This table will explain
    +---------------+---------+
    | Candidate Cat | Is Cat? |
    +---------------+---------+
    | 🐈             | Yes     |
    | 🐕             | No      |
    +---------------+---------+
