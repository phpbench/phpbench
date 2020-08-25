PhpBench
========

![PHPBench Logo](https://avatars3.githubusercontent.com/u/12785153?v=3&s=100)
[![Build Status](https://travis-ci.org/phpbench/phpbench.svg?branch=master)](https://travis-ci.org/phpbench/phpbench)

PhpBench is a benchmarking framework for PHP.

Features:

- Nice command line interface.
- Generate reports and render them to different mediums (Console, HTML,
  Markdown, etc).
- Benchmarks executed in a separate process, no effective overhead,
  no autoloader pollution.
- Control stability of results with multiple iterations and retry threshold.
- Memory usage statistics.
- Records environment (e.g. VCS info, OS info, etc.)
- Standard deviation and Z-Scores.
- Kernel density estimate used to estimate the mode.
- Time unit specification.
- Output mode specification (throughput, average time).
- Parameterized benchmarking cases.
- Per-project configuration.
- Store and reference results.
- Before and After method / class specification.
- Pause (recover) between iterations.
- Assertions.
- Extendable.
- More..

Installation Instructions
-------------------------

You can install PHPBench either as as [a
PHAR](http://phpbench.readthedocs.org/en/latest/installing.html#install-as-a-phar-package)
or as a project dependency.

Installing as a PHAR allows you to easily self-update to the latest. bleeding edge, version.

See the [installation instructions](http://phpbench.readthedocs.org/en/latest/installing.html#install-as-a-phar-package) for
information on both methods of installation.

Documentation
-------------

See the [official documentation](http://phpbench.readthedocs.org).

Community
---------

- Follow [@phpbench](https://twitter.com/phpbench) for the latest news.
- Join the `#phpbench` channel on the Slack [Symfony
  Devs](https://symfony-devs.slack.com/join/shared_invite/enQtMzM3NDA1NzEyMzg0LTgyNGYwYjFjMjY5YjllYWZkYTY2OWM4MDQzZTgzMmNjNGI3ZDJhYzE2Yjc4NmFmM2JiOTZjODg2MGJlM2RjMDU)
  channel.

Screenshots
-----------

Default output:

![phpbench-standard](https://cloud.githubusercontent.com/assets/530801/12371974/b89f3e7a-bc46-11e5-9712-40eebbd87940.png)

Aggregate report with "dots" logger:

![phpbench-aggregate](https://cloud.githubusercontent.com/assets/530801/12371973/b89c0598-bc46-11e5-93c5-882b8497fbc2.png)

Blinken logger:

![phpbench-blinken](https://cloud.githubusercontent.com/assets/530801/12371975/b8c806fc-bc46-11e5-8e05-904f1928e783.png)

HTML report:

![phpbench2](https://cloud.githubusercontent.com/assets/530801/10666918/bb61e438-78d4-11e5-8add-454c51261aa8.png)

Storage log:

![storage_log](https://cloud.githubusercontent.com/assets/530801/13897608/e9774d7e-edad-11e5-9d39-750a394e9fbf.png)

See also
--------

This library was influenced by the
[athletic](https://github.com/polyfractal/athletic) benchmarking framework.
