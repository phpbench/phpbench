

.. _generator_title_option_component:

**title**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Title for generated report

.. _generator_description_option_component:

**description**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Description for generated report

.. _generator_partition_option_component:

**partition**:
  Type(s): ``array``, Default: ``[]``

  Partition the data using these column names - the row expressions will to aggregate the data in each partition

.. _generator_components_option_component:

**components**:
  Type(s): ``array``, Default: ``[]``

  List of component configuration objects, each component must feature a ``_type`` key (e.g. ``table_aggregate``)

.. _generator_tabbed_option_component:

**tabbed**:
  Type(s): ``bool``, Default: ``false``

  Render components in tabs when supported in the output renderer (e.g. HTML)

.. _generator_tab_labels_option_component:

**tab_labels**:
  Type(s): ``array``, Default: ``[]``

  List of labels for tabs, will replace the default labels from left to right.