[![SWUbanner](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner-direct.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)

<p  align="center">
    <img src="https://user-images.githubusercontent.com/530801/92305960-d2866a00-ef83-11ea-878a-10584e583da4.png" title="PHPBench logo"/>
</p>

[![StandWithUkraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/badges/StandWithUkraine.svg)](https://github.com/vshymanskyy/StandWithUkraine/blob/main/docs/README.md)
[![CI](https://github.com/phpbench/phpbench/actions/workflows/ci.yaml/badge.svg)](https://github.com/phpbench/phpbench/actions/workflows/ci.yaml)
[![Documentation](https://github.com/phpbench/phpbench/actions/workflows/documentation.yaml/badge.svg)](https://github.com/phpbench/phpbench/actions/workflows/documentation.yaml)
[![Latest Stable Version](https://poser.pugx.org/phpbench/phpbench/v)](//packagist.org/packages/phpbench/phpbench) 
[![Total Downloads](https://poser.pugx.org/phpbench/phpbench/downloads)](//packagist.org/packages/phpbench/phpbench) 
[![License](https://poser.pugx.org/phpbench/phpbench/license)](//packagist.org/packages/phpbench/phpbench)

PHPBench is a benchmark runner for PHP analogous to
[PHPUnit](https://github.com/phpunit/phpunit) but for performance rather than
correctness.

Features include:

- [Revolutions](https://phpbench.readthedocs.io/en/latest/annotributes.html#revolutions): Repeat your code many times to determine average execution
  *time*.
- [Iterations](https://phpbench.readthedocs.io/en/latest/annotributes.html#iterations): Sample your revolutions many times and review aggregated
  statistical data.
- **Process Isolation**: Each iteration is executed in a separate process.
- [Reporting](https://phpbench.readthedocs.io/en/latest/guides/reports.html): Customizable reports and various output formats (e.g.
  console, CSV, Markdown, HTML).
- Report [storage](https://phpbench.readthedocs.io/en/latest/guides/storage.html) and [comparison](https://phpbench.readthedocs.io/en/latest/guides/regression-testing.html): Store benchmarks locally to be used as a
  baseline reference, or to reference them later.
- **Memory Usage**: Keep an eye on the amount of memory used by benchmarking
  subjects.
- [Assertions](https://phpbench.readthedocs.io/en/latest/annotributes.html#assertions): Assert that code is performing within acceptable limits, or
  that it has not regressed from a previously recorded baseline.

See the [documentation](https://phpbench.readthedocs.io/en/latest/index.html)
to find out more.

Installation
------------

```bash
composer require phpbench/phpbench --dev
```

See the [installation instructions](http://phpbench.readthedocs.io/en/latest/installing.html) for more options.

Documentation
-------------

Documentation is hosted on [readthedocs](http://phpbench.readthedocs.io).

Community
---------

- Follow [@phpbench](https://twitter.com/phpbench) for the latest news.
- Join the `#phpbench` channel on the Slack [Symfony
  Devs](https://symfony-devs.slack.com/join/shared_invite/enQtMzM3NDA1NzEyMzg0LTgyNGYwYjFjMjY5YjllYWZkYTY2OWM4MDQzZTgzMmNjNGI3ZDJhYzE2Yjc4NmFmM2JiOTZjODg2MGJlM2RjMDU)
  channel.

Screenshots
-----------

Running benchmarks and comparing against a baseline:

![phpbench](https://user-images.githubusercontent.com/530801/117569074-b52e1080-b0bb-11eb-8c80-a89ce9cce1e2.png)

Aggregated report:

![image](https://user-images.githubusercontent.com/530801/117569081-c8d97700-b0bb-11eb-91e5-fc9eaa1ac157.png)

Blinken logger:

![phpbench-blinken](https://user-images.githubusercontent.com/530801/92305786-4a539500-ef82-11ea-8a2c-db67968113b5.png)

HTML Bar Chart:

![Untitled](https://user-images.githubusercontent.com/530801/129060591-1dd984f1-8d03-4cf5-9601-7e677029e647.png)

Console Bar Chart:

![Untitled](https://user-images.githubusercontent.com/530801/129060527-9cf6c0e0-15f8-4f38-a8e1-39b257ff62fd.png)

Contributing
------------

PHPBench is an open source project. If you find a problem or want to discuss
new features or improvements please create an issue, and/or if possible create
a pull request.
