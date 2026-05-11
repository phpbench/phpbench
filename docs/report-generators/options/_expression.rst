
**title**:
    Type(s): ``[null, string]``, Default: ``NULL``

  Title to use for report

**description**:
    Type(s): ``[null, string]``, Default: ``NULL``

  Description to use for report

**cols**:
    Type(s): ``[array, null]``, Default: ``NULL``

  Columns to display

**expressions**:
    Type(s): ``array``, Default: ``[]``

  Map from column names to expressions

**baseline_expressions**:
    Type(s): ``array``, Default: ``[]``

  When the baseline is used, expressions here will be merged with the ``expressions``.

**aggregate**:
    Type(s): ``array``, Default: ``[suite_tag, benchmark_class, subject_name, variant_index]``

  Group rows by these columns

**break**:
    Type(s): ``array``, Default: ``[]``

  Group tables by these columns

**include_baseline**:
    Type(s): ``bool``, Default: ``false``

  If the baseline should be included as additional rows, or if it should be inlined

.. _generator_expression_option_hide_cols_with_missing_vars:

**hide_cols_with_missing_vars**:
  Type(s): ``bool``, Default: ``true``

  Hide columns which would have produced a "variable not found" error