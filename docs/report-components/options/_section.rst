
.. _component_section_option_title:

**title**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Title for generated report

.. _component_section_option_description:

**description**:
  Type(s): ``[string, null]``, Default: ``NULL``

  Description for generated report

.. _component_section_option_partition:

**partition**:
  Type(s): ``string[]``, Default: ``[]``

  Partition the data using these column names - components will be rendered for each partition

.. _component_section_option_filter:

**filter**:
  Type(s): ``[string, null]``, Default: ``NULL``

  If provided, filter our rows which do not match this expression

.. _component_section_option_components:

**components**:
  Type(s): ``array[]``, Default: ``[]``

  List of component configuration objects, each component must feature a ``component`` key (e.g. ``table_aggregate``)

.. _component_section_option_tabbed:

**tabbed**:
  Type(s): ``bool``, Default: ``false``

  Render components in tabs when supported in the output renderer (e.g. HTML)

.. _component_section_option_tab_labels:

**tab_labels**:
  Type(s): ``string[]``, Default: ``[]``

  List of labels for tabs, will replace the default labels from left to right.