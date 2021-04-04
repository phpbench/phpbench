Configuration
=============

CoreExtension
-------------

.. _config_bootstrap:

bootstrap
~~~~~~~~~

Default: ``null``

.. _config_path:

path
~~~~

Default: ``null``

.. _config_reports:

reports
~~~~~~~

Default: ``[]``

.. _config_outputs:

outputs
~~~~~~~

Default: ``[]``

.. _config_executors:

executors
~~~~~~~~~

Default: ``[]``

.. _config_config_path:

config_path
~~~~~~~~~~~

Default: ``null``

.. _config_progress:

progress
~~~~~~~~

Default: ``"verbose"``

.. _config_retry_threshold:

retry_threshold
~~~~~~~~~~~~~~~

Default: ``null``

.. _config_time_unit:

time_unit
~~~~~~~~~

Default: ``"microseconds"``

.. _config_output_mode:

output_mode
~~~~~~~~~~~

Default: ``"time"``

.. _config_storage:

storage
~~~~~~~

Default: ``"xml"``

.. _config_subject_pattern:

subject_pattern
~~~~~~~~~~~~~~~

Default: ``"^bench"``

.. _config_env_enabled_providers:

env.enabled_providers
~~~~~~~~~~~~~~~~~~~~~

Default: ``["uname","php","opcache","unix_sysload","git","baseline"]``

.. _config_env_baselines:

env_baselines
~~~~~~~~~~~~~

Default: ``["nothing","md5","file_rw"]``

.. _config_env_baseline_callables:

env_baseline_callables
~~~~~~~~~~~~~~~~~~~~~~

Default: ``[]``

.. _config_xml_storage_path:

xml_storage_path
~~~~~~~~~~~~~~~~

Default: ``"\/home\/daniel\/www\/phpbench\/phpbench\/.phpbench\/storage"``

.. _config_php_config:

php_config
~~~~~~~~~~

Default: ``[]``

.. _config_php_binary:

php_binary
~~~~~~~~~~

Default: ``null``

.. _config_php_wrapper:

php_wrapper
~~~~~~~~~~~

Default: ``null``

.. _config_php_disable_ini:

php_disable_ini
~~~~~~~~~~~~~~~

Default: ``false``

.. _config_annotation_import_use:

annotation_import_use
~~~~~~~~~~~~~~~~~~~~~

Default: ``false``

.. _config_remote_script_path:

remote_script_path
~~~~~~~~~~~~~~~~~~

Default: ``null``

.. _config_remote_script_remove:

remote_script_remove
~~~~~~~~~~~~~~~~~~~~

Default: ``true``

.. _config_console_disable_output:

console.disable_output
~~~~~~~~~~~~~~~~~~~~~~

Default: ``false``

.. _config_console_ansi:

console.ansi
~~~~~~~~~~~~

Default: ``true``

.. _config_progress_summary_variant_format:

progress_summary_variant_format
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Default: ``"\"Mo\" ~ display_as_time(mode(variant.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~ \n\" (\" ~ rstdev(variant.time.avg) ~ \")\""``

.. _config_progress_summary_baseline_format:

progress_summary_baseline_format
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Default: ``"\"[\" ~ \n\"Mo\" ~ display_as_time(mode(variant.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~\n\" vs \" ~ \n\"Mo\" ~ display_as_time(mode(baseline.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~ \"] \" ~ \npercent_diff(mode(baseline.time.avg), mode(variant.time.avg), (rstdev(variant.time.avg) * 2)) ~\n\" (\" ~ rstdev(variant.time.avg) ~ \")\""``

.. _config_annotations:

annotations
~~~~~~~~~~~

Default: ``true``

.. _config_attributes:

attributes
~~~~~~~~~~

Default: ``true``

.. _config_debug:

debug
~~~~~

Default: ``false``

.. _config_console_output_stream:

console.output_stream
~~~~~~~~~~~~~~~~~~~~~

Default: ``"php:\/\/stdout"``

.. _config_console_error_stream:

console.error_stream
~~~~~~~~~~~~~~~~~~~~

Default: ``"php:\/\/stderr"``

.. _config_runner_assert:

runner.assert
~~~~~~~~~~~~~

Default: ``null``

.. _config_runner_executor:

runner.executor
~~~~~~~~~~~~~~~

Default: ``null``

.. _config_runner_format:

runner.format
~~~~~~~~~~~~~

Default: ``null``

.. _config_runner_iterations:

runner.iterations
~~~~~~~~~~~~~~~~~

Default: ``null``

.. _config_runner_output_mode:

runner.output_mode
~~~~~~~~~~~~~~~~~~

Default: ``null``

.. _config_runner_time_unit:

runner.time_unit
~~~~~~~~~~~~~~~~

Default: ``null``

.. _config_runner_revs:

runner.revs
~~~~~~~~~~~

Default: ``null``

.. _config_runner_timeout:

runner.timeout
~~~~~~~~~~~~~~

Default: ``null``

.. _config_runner_warmup:

runner.warmup
~~~~~~~~~~~~~

Default: ``null``

.. _config_runner_retry_threshold:

runner.retry_threshold
~~~~~~~~~~~~~~~~~~~~~~

Default: ``null``

.. _config_extensions:

extensions
~~~~~~~~~~

Default: ``[]``

ExpressionExtension
-------------------

.. _config_expression_syntax_highlighting:

expression.syntax_highlighting
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Default: ``true``

.. _config_expression_theme:

expression.theme
~~~~~~~~~~~~~~~~

Default: ``"solarized"``

