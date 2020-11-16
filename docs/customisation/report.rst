Reports
=======

Report Generator
----------------

The report generators are responsible for generating an XML document which can
subsequently be transformed into various outputs, for example:

.. code-block:: xml

    <?xml version="1.0"?>
    <reports name="table">
      <report>
        <table title="suite: 1343e9c58e0ce558616d2b86283a89137be2216c, date: 2020-11-16, stime: 14:17:52">
          <cols>
            <col name="benchmark" label="benchmark"/>
            <col name="subject" label="subject"/>
          </cols>
          <group name="body">
            <row>
              <cell name="benchmark">
                <value role="primary" class="">LogBench</value>
              </cell>
              <cell name="subject">
                <value role="primary" class="">benchLog</value>
              </cell>
            </row>
          </group>
        </table>
      </report>
    </reports>

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
