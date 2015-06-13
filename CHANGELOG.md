CHANGELOG
=========

dev-master
----------

### Features

- Added `variance` column for aggregated results (#66)
- Removed redundant title for aggregated subject (#76)

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
