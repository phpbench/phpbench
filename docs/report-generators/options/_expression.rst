

.. _generator_title_option_expression:

**title**:
  Type(s): ``[null, string]``, Default: ``NULL``

  Title to use for report

.. _generator_description_option_expression:

**description**:
  Type(s): ``[null, string]``, Default: ``NULL``

  Description to use for report

.. _generator_cols_option_expression:

**cols**:
  Type(s): ``[array, null]``, Default: ``NULL``

  Columns to display

.. _generator_expressions_option_expression:

**expressions**:
  Type(s): ``array``, Default: ``[]``

  Map from column names to expressions

.. _generator_baseline_expressions_option_expression:

**baseline_expressions**:
  Type(s): ``array``, Default: ``[]``

  When the baseline is used, expressions here will be merged with the ``expressions``.

.. _generator_aggregate_option_expression:

**aggregate**:
  Type(s): ``array``, Default: ``[suite_tag, benchmark_class, subject_name, variant_name]``

  Group rows by these columns

.. _generator_break_option_expression:

**break**:
  Type(s): ``array``, Default: ``[]``

  Group tables by these columns

.. _generator_include_baseline_option_expression:

**include_baseline**:
  Type(s): ``bool``, Default: ``false``

  If the baseline should be included as additional rows, or if it should be inlined