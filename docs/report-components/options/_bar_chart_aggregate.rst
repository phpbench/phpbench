
.. _component_bar_chart_aggregate_option_title:

**title**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Title for the barchart

.. _component_bar_chart_aggregate_option_x_partition:

**x_partition**:
  Type(s): ``[string, string[]]``, Default: ``[]``

  Group by these columns on the X-Axis. The label will be the concatenation of the values of these columns by default

.. _component_bar_chart_aggregate_option_set_partition:

**set_partition**:
  Type(s): ``[string, string[]]``, Default: ``[]``

  Create separate bars for each step by partitioning the data based on these values.

.. _component_bar_chart_aggregate_option_y_axes_label:

**y_axes_label**:
  Type(s): ``string``, Default: ``yValue``

  Expression to evaluate to determine the Y-Axis label, is passed ``yValue`` (actual value of Y), ``partition`` (the set partition) and ``frame`` (the entire data frame) 

.. _component_bar_chart_aggregate_option_x_axes_label:

**x_axes_label**:
  Type(s): ``[null, string]``, Default: ``NULL``

  Expression to evaluate to determine the X-Axis label, is passed ``xValue`` (default X value according to the X-partition), ``partition`` (the x-partition), and ``frame`` (the entire data frame)

.. _component_bar_chart_aggregate_option_y_error_margin:

**y_error_margin**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Expression to evaluate to determine the error margin to show on the chart. Leave as NULL to disable the error margin

.. _component_bar_chart_aggregate_option_y_expr:

**y_expr**:
  Type(s): ``string``, Default: ``n/a``

  Expression to evaluate the Y-Axis value, e.g. ``mode(partition["result_time_avg"])``