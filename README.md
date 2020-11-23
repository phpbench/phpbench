<p  align="center">
    <img src="https://user-images.githubusercontent.com/530801/92305960-d2866a00-ef83-11ea-878a-10584e583da4.png" title="PHPBench logo"/>
</p>

[![Build Status](https://travis-ci.org/phpbench/phpbench.svg?branch=master)](https://travis-ci.org/phpbench/phpbench)
[![Latest Stable Version](https://poser.pugx.org/phpbench/phpbench/v)](//packagist.org/packages/phpbench/phpbench) 
[![Total Downloads](https://poser.pugx.org/phpbench/phpbench/downloads)](//packagist.org/packages/phpbench/phpbench) 
[![License](https://poser.pugx.org/phpbench/phpbench/license)](//packagist.org/packages/phpbench/phpbench)

PHPBench is a benchmark runner for PHP analogous to
[PHPUnit](https://github.com/phpunit/phpunit) but for performance rather than
correctness.

Features include:

- [Revolutions](https://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#revolutions): Repeat your code many times to determine average execution
  *time*.
- [Iterations](https://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#iterations): Sample your revolutions many times and review aggregated
  statistical data.
- **Process Isolation**: Each iteration is executed in a separate process.
- [Reporting](https://phpbench.readthedocs.io/en/latest/reports.html): Customizable reports and various output formats (e.g.
  console, CSV, Markdown, HTML).
- Report [storage](https://phpbench.readthedocs.io/en/latest/storage.html) and [comparison](https://phpbench.readthedocs.io/en/latest/regression-testing.html): Store benchmarks locally to be used as a
  baseline reference, or to reference them later.
- **Memory Usage**: Keep an eye on the amount of memory used by benchmarking
  subjects.
- [Assertions](https://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#assertions): Assert that code is performing within acceptable limits, or
  that it has not regressed from a previously recorded baseline.

See the [documentation](https://phpbench.readthedocs.io/en/latest/index.html)
to find out more.

Installation
------------

```bash
$ composer require phpbench/phpbench --dev
```

See the [installation instructions](http://phpbench.readthedocs.org/en/latest/installing.html) for more options.

Documentation
-------------

Documentation is hosted on [readthedocs](http://phpbench.readthedocs.org).

Community
---------

- Follow [@phpbench](https://twitter.com/phpbench) for the latest news.
- Join the `#phpbench` channel on the Slack [Symfony
  Devs](https://symfony-devs.slack.com/join/shared_invite/enQtMzM3NDA1NzEyMzg0LTgyNGYwYjFjMjY5YjllYWZkYTY2OWM4MDQzZTgzMmNjNGI3ZDJhYzE2Yjc4NmFmM2JiOTZjODg2MGJlM2RjMDU)
  channel.

Screenshots
-----------

Default output:

![phpbench-standard](https://user-images.githubusercontent.com/530801/92305757-14aeac00-ef82-11ea-87b1-077afc72f0f4.png)

Blinken logger:

![phpbench-blinken](https://user-images.githubusercontent.com/530801/92305786-4a539500-ef82-11ea-8a2c-db67968113b5.png)

HTML report:

![phpbench2](https://cloud.githubusercontent.com/assets/530801/10666918/bb61e438-78d4-11e5-8add-454c51261aa8.png)

Contributing
------------

PHPBench is an open source project. If you find an problem or want to discuss
new features or improvements please create an issue, and/or if possible create
a pull request.
