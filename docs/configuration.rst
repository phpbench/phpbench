Configuration
=============

PhpBench\Extension\DevelopmentExtension
---------------------------------------

PhpBench\Extension\CoreExtension
--------------------------------

.. _bootstrap:

bootstrap
~~~~~~~~~

Default: ``null``

.. _path:

path
~~~~

Default: ``null``

.. _reports:

reports
~~~~~~~

Default: ``[]``

.. _outputs:

outputs
~~~~~~~

Default: ``[]``

.. _executors:

executors
~~~~~~~~~

Default: ``[]``

.. _config_path:

config_path
~~~~~~~~~~~

Default: ``null``

.. _progress:

progress
~~~~~~~~

Default: ``"verbose"``

.. _retry_threshold:

retry_threshold
~~~~~~~~~~~~~~~

Default: ``null``

.. _time_unit:

time_unit
~~~~~~~~~

Default: ``"microseconds"``

.. _output_mode:

output_mode
~~~~~~~~~~~

Default: ``"time"``

.. _storage:

storage
~~~~~~~

Default: ``"xml"``

.. _subject_pattern:

subject_pattern
~~~~~~~~~~~~~~~

Default: ``"^bench"``

.. _env.enabled_providers:

env.enabled_providers
~~~~~~~~~~~~~~~~~~~~~

Default: ``["uname","php","opcache","unix_sysload","git","baseline"]``

.. _env_baselines:

env_baselines
~~~~~~~~~~~~~

Default: ``["nothing","md5","file_rw"]``

.. _env_baseline_callables:

env_baseline_callables
~~~~~~~~~~~~~~~~~~~~~~

Default: ``[]``

.. _xml_storage_path:

xml_storage_path
~~~~~~~~~~~~~~~~

Default: ``"\/home\/daniel\/www\/phpbench\/phpbench\/.phpbench\/storage"``

.. _php_config:

php_config
~~~~~~~~~~

Default: ``[]``

.. _php_binary:

php_binary
~~~~~~~~~~

Default: ``null``

.. _php_wrapper:

php_wrapper
~~~~~~~~~~~

Default: ``null``

.. _php_disable_ini:

php_disable_ini
~~~~~~~~~~~~~~~

Default: ``false``

.. _annotation_import_use:

annotation_import_use
~~~~~~~~~~~~~~~~~~~~~

Default: ``false``

.. _remote_script_path:

remote_script_path
~~~~~~~~~~~~~~~~~~

Default: ``null``

.. _remote_script_remove:

remote_script_remove
~~~~~~~~~~~~~~~~~~~~

Default: ``true``

.. _console.disable_output:

console.disable_output
~~~~~~~~~~~~~~~~~~~~~~

Default: ``false``

.. _console.ansi:

console.ansi
~~~~~~~~~~~~

Default: ``true``

.. _progress_summary_variant_format:

progress_summary_variant_format
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Default: ``"\"Mo\" ~ display_as_time(mode(variant.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~ \n\" (\" ~ rstdev(variant.time.avg) ~ \")\""``

.. _progress_summary_baseline_format:

progress_summary_baseline_format
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Default: ``"\"[\" ~ \n\"Mo\" ~ display_as_time(mode(variant.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~\n\" vs \" ~ \n\"Mo\" ~ display_as_time(mode(baseline.time.avg), coalesce(subject.time_unit,\"microseconds\"), subject.time_precision, subject.time_mode) ~ \"] \" ~ \npercent_diff(mode(baseline.time.avg), mode(variant.time.avg), (rstdev(variant.time.avg) * 2)) ~\n\" (\" ~ rstdev(variant.time.avg) ~ \")\""``

.. _annotations:

annotations
~~~~~~~~~~~

Default: ``true``

.. _attributes:

attributes
~~~~~~~~~~

Default: ``true``

.. _debug:

debug
~~~~~

Default: ``false``

.. _console.output_stream:

console.output_stream
~~~~~~~~~~~~~~~~~~~~~

Default: ``"php:\/\/stdout"``

.. _console.error_stream:

console.error_stream
~~~~~~~~~~~~~~~~~~~~

Default: ``"php:\/\/stderr"``

.. _runner.assert:

runner.assert
~~~~~~~~~~~~~

Default: ``null``

.. _runner.executor:

runner.executor
~~~~~~~~~~~~~~~

Default: ``null``

.. _runner.format:

runner.format
~~~~~~~~~~~~~

Default: ``null``

.. _runner.iterations:

runner.iterations
~~~~~~~~~~~~~~~~~

Default: ``null``

.. _runner.output_mode:

runner.output_mode
~~~~~~~~~~~~~~~~~~

Default: ``null``

.. _runner.time_unit:

runner.time_unit
~~~~~~~~~~~~~~~~

Default: ``null``

.. _runner.revs:

runner.revs
~~~~~~~~~~~

Default: ``null``

.. _runner.timeout:

runner.timeout
~~~~~~~~~~~~~~

Default: ``null``

.. _runner.warmup:

runner.warmup
~~~~~~~~~~~~~

Default: ``null``

.. _runner.retry_threshold:

runner.retry_threshold
~~~~~~~~~~~~~~~~~~~~~~

Default: ``null``

.. _extensions:

extensions
~~~~~~~~~~

Default: ``[]``

PhpBench\Extension\ExpressionExtension
--------------------------------------

.. _expression.syntax_highlighting:

expression.syntax_highlighting
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Default: ``true``

.. _expression.theme:

expression.theme
~~~~~~~~~~~~~~~~

Default: ``"solarized"``

