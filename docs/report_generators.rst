Report Generators
=================

PHPBench generates reports using report generators. These are classes which
implement the ``PhpBench\Report\GeneratorInterface``.

This chapter will describe the default report generators.

.. _generator_console_table:

``console_table``
-----------------

Generates a tabular report directly on the console.

Class: ``PhpBench\Report\Generator\ConsoleTabularGenerator``.

Options:

- **title**: *(string)* Display this title.
- **description**: *(string)* Display this description.
- **aggregate**: *(boolean)* Aggregate iterations into a single row.
- **exclude**: *(array)* List of columns to exclude.
- **debug**: *(boolean)* Show XML.
- **sort**: *(assoc array)* Associative array of columns to directions for
  sorting, e.g. `{"subject": "asc", "time": "asc"}`.
- **selector**: *(string)* XPath selector to use when selecting the benchmark results.
- **groups**: *(array)* Only include the named groups.

The ``selector`` option is important and can be used to target specific
results, for example ``//subject[group/@name="my_group"]`` would only report
subjects in the group ``my_group``.

.. _generator_console_table_custom:

``console_table_custom``
------------------------

Also generates a tabular console report but allows you to specify a `Tabular
definition`_ file in order to have complete control over the generated report.

Class: ``PhpBench\Report\Generator\ConsoleTabularCustomGenerator``.

Options:

- **title**: *(string)* Display this title.
- **description**: *(string)* Display this description.
- **file**: *(string)* Name of tabular definition file.
- **params**: *(object)* Associative array of parameters to pass to Tabular.
- **debug**: *(boolean)* Show XML.

``composite``
-------------

This report generates multiple reports.

Class: ``PhpBench\Report\Generator\CompositeGenerator``.

Options:

- **reports**: *(array)*: List of report names.

.. _Tabular definition: http://tabular.readthedocs.org/en/master/definition.html
