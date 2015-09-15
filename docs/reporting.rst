Reporting
=========

PHPBench supports report generation. Reports are generated using "report
generators", and reports themselves are just configurations for the report
generators.

When this documentation talks about *reports* we are referring to the report
configuration, not the generators.

This chapter will describe the default report generators.

``console_table``
-----------------

Generates a tabular report directly on the console.

Options:

- **title**: *(string)* Display this title.
- **description**: *(string)* Display this description.
- **aggregate**: *(boolean)* Aggregate iterations into a single row.
- **exclude**: *(array)* List of columns to exclude.
- **debug**: *(boolean)* Show XML.
- **selector**: *(string)* XPath selector to use when selecting the benchmark results.

The ``selector`` option is important and can be used to target specific
results, for example ``//subject[group/@name="my_group"]`` would only report
subjects in the group ``my_group``.

``console_table_custom``
------------------------

Also generates a tabular console report but allows you to specify a `Tabular
definition`_ file in order to have complete control over the generated report.

Options:

- **title**: *(string)* Display this title.
- **description**: *(string)* Display this description.
- **file**: *(string)* Name of tabular definition file.
- **params**: *(object)* Associative array of parameters to pass to Tabular.
- **debug**: *(boolean)* Show XML.

``composite``
-------------

This report generates multiple reports.

Options:

- **reports**: *(array)*: List of report names.

.. _Tabular definition: http://tabular.readthedocs.org/en/master/definition.html
