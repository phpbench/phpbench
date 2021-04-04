Configuration
=============

CoreExtension
-------------

.. _configuration_bootstrap:

bootstrap
~~~~~~~~~

Default: ``null``

Types: ``["string","null"]``

.. _configuration_path:

path
~~~~

Default: ``null``

Types: ``["string","array","null"]``

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

.. _configuration_executors:

executors
~~~~~~~~~

Default: ``[]``

Types: ``["array"]``

.. _configuration_config_path:

config_path
~~~~~~~~~~~

Default: ``null``

Types: ``["string","null"]``

.. _configuration_progress:

progress
~~~~~~~~

Default: ``"verbose"``

Types: ``["string"]``

.. _configuration_retry_threshold:

retry_threshold
~~~~~~~~~~~~~~~

Default: ``null``

Types: ``["null","int","float"]``

.. _configuration_time_unit:

time_unit
~~~~~~~~~

Default: ``"microseconds"``

Types: ``["string"]``

.. _configuration_output_mode:

output_mode
~~~~~~~~~~~

Default: ``"time"``

Types: ``["string"]``

.. _configuration_storage:

storage
~~~~~~~

Default: ``"xml"``

Types: ``["string"]``

.. _configuration_subject_pattern:

subject_pattern
~~~~~~~~~~~~~~~

Default: ``"^bench"``

Types: ``["string"]``

.. _configuration_env_enabled_providers:

env.enabled_providers
~~~~~~~~~~~~~~~~~~~~~

Default: ``["uname","php","opcache","unix_sysload","git","baseline"]``

Types: ``["array"]``

.. _configuration_env_baselines:

env_baselines
~~~~~~~~~~~~~

Default: ``["nothing","md5","file_rw"]``

Types: ``["array"]``

.. _configuration_env_baseline_callables:

env_baseline_callables
~~~~~~~~~~~~~~~~~~~~~~

Default: ``[]``

Types: ``["array"]``

.. _configuration_xml_storage_path:

xml_storage_path
~~~~~~~~~~~~~~~~

Default: ``"\/home\/daniel\/www\/phpbench\/phpbench\/.phpbench\/storage"``

Types: ``["string"]``

.. _configuration_php_config:

php_config
~~~~~~~~~~

Default: ``[]``

Types: ``["array"]``

.. _configuration_php_binary:

php_binary
~~~~~~~~~~

Default: ``null``

Types: ``["string","null"]``

.. _configuration_php_wrapper:

php_wrapper
~~~~~~~~~~~

Default: ``null``

Types: ``["string","null"]``

.. _configuration_php_disable_ini:

php_disable_ini
~~~~~~~~~~~~~~~

Default: ``false``

Types: ``["bool"]``

.. _configuration_annotation_import_use:

annotation_import_use
~~~~~~~~~~~~~~~~~~~~~

Default: ``false``

Types: ``["bool"]``

.. _configuration_remote_script_path:

remote_script_path
~~~~~~~~~~~~~~~~~~

Default: ``null``

Types: ``["string","null"]``

.. _configuration_remote_script_remove:

remote_script_remove
~~~~~~~~~~~~~~~~~~~~

Default: ``true``

Types: ``["bool"]``

.. _configuration_console_disable_output:

console.disable_output
~~~~~~~~~~~~~~~~~~~~~~

Default: ``false``

Types: ``["bool"]``

.. _configuration_console_ansi:

console.ansi
~~~~~~~~~~~~

Default: ``true``

Types: ``["bool"]``

.. _configuration_progress_summary_variant_format:

progress_summary_variant_format
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Default: ``"\"Mo\" ~ display_as_time(mode(variant.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~ \n\" (\" ~ rstdev(variant.time.avg) ~ \")\""``

Types: ``["string"]``

.. _configuration_progress_summary_baseline_format:

progress_summary_baseline_format
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Default: ``"\"[\" ~ \n\"Mo\" ~ display_as_time(mode(variant.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~\n\" vs \" ~ \n\"Mo\" ~ display_as_time(mode(baseline.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~ \"] \" ~ \npercent_diff(mode(baseline.time.avg), mode(variant.time.avg), (rstdev(variant.time.avg) * 2)) ~\n\" (\" ~ rstdev(variant.time.avg) ~ \")\""``

Types: ``["string"]``

.. _configuration_annotations:

annotations
~~~~~~~~~~~

Default: ``true``

Types: ``["bool"]``

.. _configuration_attributes:

attributes
~~~~~~~~~~

Default: ``true``

Types: ``["bool"]``

.. _configuration_debug:

debug
~~~~~

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

.. _configuration_runner_assert:

runner.assert
~~~~~~~~~~~~~

Default: ``null``

Types: ``["null","string","array"]``

.. _configuration_runner_executor:

runner.executor
~~~~~~~~~~~~~~~

Default: ``null``

Types: ``["null","string"]``

.. _configuration_runner_format:

runner.format
~~~~~~~~~~~~~

Default: ``null``

Types: ``["null","string"]``

.. _configuration_runner_iterations:

runner.iterations
~~~~~~~~~~~~~~~~~

Default: ``null``

Types: ``["null","int","array"]``

.. _configuration_runner_output_mode:

runner.output_mode
~~~~~~~~~~~~~~~~~~

Default: ``null``

Types: ``["null","string"]``

.. _configuration_runner_time_unit:

runner.time_unit
~~~~~~~~~~~~~~~~

Default: ``null``

Types: ``["null","string"]``

.. _configuration_runner_revs:

runner.revs
~~~~~~~~~~~

Default: ``null``

Types: ``["null","int","array"]``

.. _configuration_runner_timeout:

runner.timeout
~~~~~~~~~~~~~~

Default: ``null``

Types: ``["null","float","int"]``

.. _configuration_runner_warmup:

runner.warmup
~~~~~~~~~~~~~

Default: ``null``

Types: ``["null","int","array"]``

.. _configuration_runner_retry_threshold:

runner.retry_threshold
~~~~~~~~~~~~~~~~~~~~~~

Default: ``null``

Types: ``["null","int","float"]``

.. _configuration_extensions:

extensions
~~~~~~~~~~

Default: ``[]``

Types: ``["array"]``

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

