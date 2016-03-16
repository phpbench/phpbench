Environment
===========

PHPBench will try and record as much information about the current environment
as it can. This is facilitated by "environment provider" classes which
implement the ``PhpBench\\Environment\\ProviderInterface`` and are registered
with the ``environment_provider`` tag in the DI container.

This information is recorded in the XML document:

.. code-block:: xml

    <env>
      <uname os="Linux" host="dtlt410" release="4.2.0-1-amd64" version="#1 SMP Debian 4.2.6-1 (2015-11-10)" machine="x86_64"/>
      <php version="5.6.15-1"/>
      <unix-sysload l1="1.04" l5="0.63" l15="0.55"/>
      <vcs system="git" branch="env_info" version="edde9dc7542cfa8e3ef4da459f0aaa5dfb095109"/>
    </env>

This information can be readily viewed with the :ref:`report_env` report and can also be
displayed when using the :ref:`table report generator <generator_table>`.

GIT
---

**Class**: ``PhpBench\\Environment\\Provider\\Git``.
**Available**: When PHPBench is run in the *root* directory of a GIT
repository.

The GIT provider will provide VCS information, including the branch and
vesion (i.e. the ``commitsh``).

PHP
---

**Class**: ``PhpBench\\Environment\\Provider\\Php``.
**Available**: Always

Provides the PHP version.

Uname
-----

**Class**: ``PhpBench\\Environment\\Provider\\Uname``.
**Available**: Always

Provides information about the operating system obtained through the
`php_uname`_ command.

Unix Sysload
------------

**Class**: ``PhpBench\\Environment\\Provider\\UnixSysload``.
**Available**: On non-windows systems.

Provides the `CPU load`_ for the following time periods: 1 minute, 5 minutes and
15 minutes.

Baseline
--------

**Class**: ``PhpBench\Environment\Provider\Baseline``
**Available**: Always

Provides baseline measurements, by default it will provide mean times for
executing the following micro-benchmarks (1000 revolutions):

- ``nothing``: An empty method.
- ``md5``: Calculation of an MD5 hash.
- ``file_rw``: File read and write.

These measurements can help determine the relative speed of the system under
test compared to other systems.

.. _CPU load: https://en.wikipedia.org/wiki/Load_(computing)
.. _php_uname: http://php.net/manual/en/function.php-uname.php
