
.. _generator_component_option_title:

**title**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Title for generated report

.. _generator_component_option_description:

**description**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Description for generated report

.. _generator_component_option_partition:

**partition**:
  Type(s): ``string[]``, Default: ``[]``

  Partition the data using these column names - the row expressions will to aggregate the data in each partition

.. _generator_component_option_components:

**components**:
  Type(s): ``array[]``, Default: ``[]``

  List of component configuration objects, each component must feature a ``component`` key (e.g. ``table_aggregate``)

.. _generator_component_option_tabbed:

**tabbed**:
  Type(s): ``bool``, Default: ``false``

  Render components in tabs when supported in the output renderer (e.g. HTML)

.. _generator_component_option_tab_labels:

**tab_labels**:
  Type(s): ``string[]``, Default: ``[]``

  List of labels for tabs, will replace the default labels from left to right.