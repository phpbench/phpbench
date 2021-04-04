Configuration
=============

.. include:: configuration-prelude.rst

CoreExtension
-------------

.. _configuration_console_ansi:

console.ansi
~~~~~~~~~~~~



Default: ``true``

Types: ``["bool"]``

.. _configuration_console_disable_output:

console.disable_output
~~~~~~~~~~~~~~~~~~~~~~



Default: ``false``

Types: ``["bool"]``

.. _configuration_console_output_stream:

console.output_stream
~~~~~~~~~~~~~~~~~~~~~



Default: ``"php:\/\/stdout"``

Types: ``["string"]``

.. _configuration_console_error_stream:

console.error_stream
~~~~~~~~~~~~~~~~~~~~



Default: ``"php:\/\/stderr"``

Types: ``["string"]``

.. _configuration_debug:

debug
~~~~~



Default: ``false``

Types: ``["bool"]``

.. _configuration_extensions:

extensions
~~~~~~~~~~



Default: ``[]``

Types: ``["array"]``

.. _configuration_output_mode:

output_mode
~~~~~~~~~~~



Default: ``"time"``

Types: ``["string"]``

.. _configuration_time_unit:

time_unit
~~~~~~~~~



Default: ``"microseconds"``

Types: ``["string"]``

.. _configuration_storage:

storage
~~~~~~~



Default: ``"xml"``

Types: ``["string"]``

.. _configuration_xml_storage_path:

xml_storage_path
~~~~~~~~~~~~~~~~



Default: ``".phpbench\/storage"``

Types: ``["string"]``

.. _configuration_config_path:

config_path
~~~~~~~~~~~



Default: ``null``

Types: ``["string","null"]``

.. _configuration_reports:

reports
~~~~~~~



Default: ``[]``

Types: ``["array"]``

.. _configuration_outputs:

outputs
~~~~~~~



Default: ``[]``

Types: ``["array"]``

RunnerExtension
---------------

.. _configuration_annotations:

annotations
~~~~~~~~~~~

Read metadata from annotations

Default: ``true``

Types: ``["bool"]``

.. _configuration_annotation_import_use:

annotation_import_use
~~~~~~~~~~~~~~~~~~~~~

Require that annotations be imported before use

Default: ``false``

Types: ``["bool"]``

.. _configuration_attributes:

attributes
~~~~~~~~~~

Read metadata from PHP 8 attributes

Default: ``true``

Types: ``["bool"]``

.. _configuration_bootstrap:

bootstrap
~~~~~~~~~

Path to bootstrap (e.g. ``vendor/autoload.php``)

Default: ``null``

Types: ``["string","null"]``

.. _configuration_env_enabled_providers:

env.enabled_providers
~~~~~~~~~~~~~~~~~~~~~

Explicitly enable environment samplers, ``null`` enables all

Default: ``["baseline","git","opcache","php","uname","unix_sysload"]``

Types: ``["array"]``

.. _configuration_env_baselines:

env_baselines
~~~~~~~~~~~~~

Environment baselines (not to be confused with baseline comparisons when running benchmarks) are small benchmarks which run to sample the speed of the system (e.g. file I/O, computation etc). This setting enables or disables these baselines

Default: ``["nothing","md5","file_rw"]``

Types: ``["array"]``

.. _configuration_env_baseline_callables:

env_baseline_callables
~~~~~~~~~~~~~~~~~~~~~~

Map of baseline callables (adds you to register a new environemntal baseline)

Default: ``[]``

Types: ``["array"]``

.. _configuration_executors:

executors
~~~~~~~~~

Add new executor configurations

Default: ``[]``

Types: ``["array"]``

.. _configuration_path:

path
~~~~

Path or paths to the benchmarks

Default: ``null``

Types: ``["string","array","null"]``

.. _configuration_php_binary:

php_binary
~~~~~~~~~~

Specify a PHP binary to use when executing out-of-band benchmarks, e.g. ``/usr/bin/php6``, defaults to the version of PHP used to invoke PHPBench

Default: ``null``

Types: ``["string","null"]``

.. _configuration_php_config:

php_config
~~~~~~~~~~

Map of PHP ini settings to use when executing out-of-band benchmarks

Default: ``[]``

Types: ``["array"]``

.. _configuration_php_disable_ini:

php_disable_ini
~~~~~~~~~~~~~~~

Disable reading the default PHP configuration

Default: ``false``

Types: ``["bool"]``

.. _configuration_php_wrapper:

php_wrapper
~~~~~~~~~~~

Wrap the PHP binary with this command (e.g. ``blackfire run``)

Default: ``null``

Types: ``["string","null"]``

.. _configuration_progress:

progress
~~~~~~~~

Default progress logger to use

Default: ``"verbose"``

Types: ``["string"]``

.. _configuration_progress_summary_variant_format:

progress_summary_variant_format
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Expression used to render the summary text default progress loggers

Default: ``"\"Mo\" ~ display_as_time(mode(variant.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~ \n\" (\" ~ rstdev(variant.time.avg) ~ \")\""``

Types: ``["string"]``

.. _configuration_progress_summary_baseline_format:

progress_summary_baseline_format
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When the a comparison benchmark is referenced, alternative expression used to render the summary text default progress loggers

Default: ``"\"[\" ~ \n\"Mo\" ~ display_as_time(mode(variant.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~\n\" vs \" ~ \n\"Mo\" ~ display_as_time(mode(baseline.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~ \"] \" ~ \npercent_diff(mode(baseline.time.avg), mode(variant.time.avg), (rstdev(variant.time.avg) * 2)) ~\n\" (\" ~ rstdev(variant.time.avg) ~ \")\""``

Types: ``["string"]``

.. _configuration_remote_script_path:

remote_script_path
~~~~~~~~~~~~~~~~~~

PHPBench generates a PHP file for out-of-band benchmarks which is executed, this setting specifies the path to this file. When NULL a file in the systems temporary directory will be used

Default: ``null``

Types: ``["string","null"]``

.. _configuration_remote_script_remove:

remote_script_remove
~~~~~~~~~~~~~~~~~~~~

If the generated file should be removed after it has been executed (useful for debugging)

Default: ``true``

Types: ``["bool"]``

.. _configuration_retry_threshold:

retry_threshold
~~~~~~~~~~~~~~~

DEPRECATED: use :ref:`configuration_runner_retry_threshold` instead

Default: ``null``

Types: ``["null","int","float"]``

.. _configuration_runner_assert:

runner.assert
~~~~~~~~~~~~~

Default assertion

Default: ``null``

Types: ``["null","string","array"]``

.. _configuration_runner_executor:

runner.executor
~~~~~~~~~~~~~~~

Default executor

Default: ``null``

Types: ``["null","string"]``

.. _configuration_runner_format:

runner.format
~~~~~~~~~~~~~

Default format

Default: ``null``

Types: ``["null","string"]``

.. _configuration_runner_iterations:

runner.iterations
~~~~~~~~~~~~~~~~~

Default number of iterations

Default: ``null``

Types: ``["null","int","array"]``

.. _configuration_runner_output_mode:

runner.output_mode
~~~~~~~~~~~~~~~~~~

Default output mode (e.g. throughput)

Default: ``null``

Types: ``["null","string"]``

.. _configuration_runner_time_unit:

runner.time_unit
~~~~~~~~~~~~~~~~

Default time unit

Default: ``null``

Types: ``["null","string"]``

.. _configuration_runner_retry_threshold:

runner.retry_threshold
~~~~~~~~~~~~~~~~~~~~~~

Default retry threshold

Default: ``null``

Types: ``["null","int","float"]``

.. _configuration_runner_revs:

runner.revs
~~~~~~~~~~~

Default number of revolutions

Default: ``null``

Types: ``["null","int","array"]``

.. _configuration_runner_timeout:

runner.timeout
~~~~~~~~~~~~~~

Default timeout

Default: ``null``

Types: ``["null","float","int"]``

.. _configuration_runner_warmup:

runner.warmup
~~~~~~~~~~~~~

Default warmup

Default: ``null``

Types: ``["null","int","array"]``

.. _configuration_subject_pattern:

subject_pattern
~~~~~~~~~~~~~~~

Subject prefix to use when finding benchmarks

Default: ``"^bench"``

Types: ``["string"]``

ExpressionExtension
-------------------

.. _configuration_expression_syntax_highlighting:

expression.syntax_highlighting
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~



Default: ``true``

Types: ``["bool"]``

.. _configuration_expression_theme:

expression.theme
~~~~~~~~~~~~~~~~



Default: ``"solarized"``

Types: ``["string"]``

