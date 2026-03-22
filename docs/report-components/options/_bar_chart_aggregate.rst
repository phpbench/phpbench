
**title**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Title for the barchart

**x_partition**:
  Type(s): ``[string, string[]]``, Default: ``[]``

  Partition the data for aggregation on the X axes. Partitions are made of rows sharing the same values in the expression or columns (which can be expressions) given here.

**bar_partition**:
  Type(s): ``[string, string[]]``, Default: ``[]``

  Partition the individual bars for each X partition.

**y_axes_label**:
  Type(s): ``string``, Default: ``yValue``

  Expression to evaluate the Y-Axis label. It is passed ``yValue`` (actual value of Y), ``partition`` (the set partition) and ``frame`` (the entire data frame) 

**x_axes_label**:
  Type(s): ``[null, string]``, Default: ``NULL``

  Expression to evaluate the X-Axis label, is passed ``xValue`` (default X value according to the X-partition), ``partition`` (the x-partition), and ``frame`` (the entire data frame)

**y_error_margin**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Expression to evaluate to determine the error margin to show on the chart. Leave as NULL to disable the error margin

**y_expr**:
  Type(s): ``string``, Default: ``n/a``

  Expression to evaluate the Y-Axis value, e.g. ``mode(partition["result_time_avg"])``