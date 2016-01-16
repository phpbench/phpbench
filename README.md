PhpBench
========

![PHPBench Logo](https://avatars3.githubusercontent.com/u/12785153?v=3&s=100)
[![Build Status](https://travis-ci.org/phpbench/phpbench.svg?branch=master)](https://travis-ci.org/phpbench/phpbench)
[![StyleCI](https://styleci.io/repos/34982189/shield)](https://styleci.io/repos/34982189)

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
- Serialize results as XML and generate reports later.
- Before and After method / class specification.
- Pause (recover) between iterations.
- Extendable.
- More..

Documentation and Installation Instructions
-------------------------------------------

See the [official documentation](http://phpbench.readthedocs.org)

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

See also
--------

This library was influenced by the
[athletic](https://github.com/polyfractal/athletic) benchmarking framework.
