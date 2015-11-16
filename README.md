PhpBench
========

![PHPBench Logo](https://avatars3.githubusercontent.com/u/12785153?v=3&s=100)
[![Build Status](https://travis-ci.org/phpbench/phpbench.svg?branch=master)](https://travis-ci.org/phpbench/phpbench)
[![StyleCI](https://styleci.io/repos/34982189/shield)](https://styleci.io/repos/34982189)

PhpBench is a benchmarking framework for PHP.

Features:

- Generate reports and render them to different mediums (Console, HTML,
  Markdown, etc).
- Benchmarks executed in a separate process, no effective overhead,
  no autoloader polution.
- Control stability of results with multiple iterations and retry threshold.
- Memory usage statistics in addition to time.
- Parameterized benchmarking cases.
- Per-project configuration.
- Serialize results as XML and generate reports later.
- Before and After method specification.
- Pause (recover) between iterations
- Nice command line interface.
- Fully extendable.
- Utilizes [Tabular](https://github.com/phpbench/Tabular) for creating custom
  reports.
- More..

Documentation and Installation Instructions
-------------------------------------------

See the [official documentation](http://phpbench.readthedocs.org)

Screenshots
-----------

Aggregate reporte:

![Screenshot](https://cloud.githubusercontent.com/assets/530801/10666674/68546546-78d3-11e5-98be-14ebda9eefa3.png)

Verbose logging with customized report:

![Screenshot](https://cloud.githubusercontent.com/assets/530801/10666797/1c55583e-78d4-11e5-844d-a9acbeb4ae6d.png)

HTML report:

![phpbench2](https://cloud.githubusercontent.com/assets/530801/10666918/bb61e438-78d4-11e5-8add-454c51261aa8.png)

See also
--------

This library was influenced by the
[athletic](https://github.com/polyfractal/athletic) benchmarking framework.
