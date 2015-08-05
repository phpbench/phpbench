CHANGELOG
=========

0.5
---

- Configuration changed to JSON format.
- Dependency Injection Container.
- Extension API.
- Major refactoring of reports, old console report does not exist.
- Separation of reports and generators. Reports are registered as
  configurations, reports must be explicitly called on the CLI in order for
  them to be generated.
- New "composite" report for generating multiple reports at one time.
- Removal of the "description" annotation.
- Removed OptionsResolver, replaced functionality with JSON schema to allow
  validation of nested report configurations.

0.4
---

- Ability to place parameter tokens in descriptions
- Each parameter set now considered as a separate subject
- Removed default report.
- Show memory in `simple_table` report (#87).
- `footer` option now available to show aggregate values for columns (#86)

0.3
---

### Enhancements

- Improved formatting for console table report (#80)
- Sorting on multiple columns (`console_table` report) Sort accepts an array, e.g. `array('col1' => 'asc', 'col2' => 'desc')`. (#72)
- Separate revolution sets run in separate processes when required.

### Features

- New "simple" report, with no options. Used by default.
- Do not show empty reports on the in the `console_table` report
- Added `variance` column for aggregated results (#66)
- Removed redundant title for aggregated subject (#76)
- Added `subject_meta` option to console report generator.

### Bugs

- (console) report is generated when dumping to stdout with a configuration
- Options resolver dependency is too low (#59)
- HHVM build fails

0.2
---

### Features

- Bumped minimum version of PHP to 5.4
- [Report] Function `avg` renamed to `mean`
- [Report] `aggregate_iterations` changed to `aggregate`
- [Report] Aggregate on either runs or subjects
- [Report] Explicit column name selection
- [Report] Deviation step
- [Report] Added `sort` and `sort_dir` options
- [Report] Added `groups` option to run report only on specified groups
- [RunCommand] Slugified option names
