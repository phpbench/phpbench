
**title**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Caption for the table

**partition**:
  Type(s): ``[string, string[]]``, Default: ``[]``

  Partition the data using these column names - the row expressions will to aggregate the data in each partition

**row**:
  Type(s): ``array``, Default: ``[]``

  Set of expressions used to evaluate the partitions, the key is the column name, the value is the expression

**groups**:
  Type(s): ``array``, Default: ``[]``

  Group columns together, e.g. ``{"groups":{"group_name": {"cols": ["col1", "col2"]}}}``