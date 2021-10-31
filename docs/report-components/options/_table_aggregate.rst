
.. _component_table_aggregate_option_title:

**title**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Caption for the table

.. _component_table_aggregate_option_partition:

**partition**:
  Type(s): ``[string, string[]]``, Default: ``[]``

  Partition the data using these column names - the row expressions will to aggregate the data in each partition

.. _component_table_aggregate_option_row:

**row**:
  Type(s): ``array``, Default: ``[]``

  Set of expressions used to evaluate the partitions, the key is the column name, the value is the expression

.. _component_table_aggregate_option_groups:

**groups**:
  Type(s): ``array``, Default: ``[]``

  Group columns together, e.g. ``{"groups":{"group_name": {"cols": ["col1", "col2"]}}}``