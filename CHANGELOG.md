CHANGELOG
=========

1.2.10
------

- Bump minimum PHP version to 8.1
- Allow Symfony 7.0 @keulinho
- Add documentation for adding env providers @GromNaN

1.2.{11,12,13,14} (09/07/2023)
------------------------------

Improvements:

- Use latest version of Box
- Fixing build

1.2.10 (24/04/2023)
-------------------

Improvements:

- Fix deprecation warning when using blinken logger @tavaresmatheus

1.2.9
-----

Bug fixes:

- Fix PHP 8.2 deprecation warning #1022

Improvements:

- Drop support for 7.3 and support for 8.1 and 8.2 in the pipeline.

1.2.8
-----

Improvements:

- Update documentation to point to reference new GPG key #1017
- Support Doctrine Annotations 2.0 #1013 @greg0ire
- Profiler: detect Xdebug `.gz` files when compression enabled #1011 @blackwolf12333

1.2.7
-----

Improvements:

- Remove dependency on `webmozart/path` (copied the class into PHPBench)
- Update PHPStan to 1.0 and fix new errors #1006
- Use `webmozart/glob` instead of `glob` for benchmark paths and config
  include paths #1005

Bug fixes:

- Fix bad exception call #1002 @TRowbotham
- Fix comma formatting of numbers with no zero decimal precision #1008

1.2.6
-----

Bug fixes:

- Allow multiple `Assert` annotations #996 @nyamsprod

1.2.5
-----

Bug fixes:

- New "CouldNotLoadMetadata" exception was located in the `tests` folder and
  was not available when phpbench was used as a dependency.

1.2.4
-----

Improvements

- Show [solidarity](https://github.com/vshymanskyy/StandWithUkraine) with Ukrainians
- Show more concise error messages including script exit code #969
- Configuration (`expression.strip_tailing_zeros`) to strip meaningless zeros after the decimal place #958

1.2.3
------

Improvements:

- Allow `psr/log` `2.x` and `3.x`

Bug fixes:

- Parameters seem to be converted to strings when storing/retrieving #959

1.2.2
-----

Bug fixes:

- Fix non-existing mappings in composer file #955 @Dgame

1.2.1
-----

Bug fixes:

- Do not index variants by parameter set name (as they vary by number of
  revs/iterations etc also) #947

Improvements:

- Symfony 6 support - @julien-boudry
- Allow globs in benchmark path specification - @ricardoboss

1.2.0
-----

Features:

- [expression] Support binary memory units #934
- [reporting] Ability to expand table columns dynamically #928
- [reporting] Ability to group columns #928
- [reporting] Added `benchmark_compare` default report #928
- [cli] Ability to filter by variant #938
- [cli] Ability to filter reports #940

Improvements:

- [storage] Allow `.` in tag names
- [runner] Do not show warning when unable to load metadata for benchmark IF
  the `runner.file_pattern` is specified #941

Bug fixes:

- Fix property (`.`) access precedence, it is now the same as `[` array
  access #928

1.1.3 (2021-10-31)
------------------

- Fix bug with misassigned labels in bar chart #931
- Safely removed non-functioning config option `report.html_output_dir` #930

1.1.2 (2021-09-25)
------------------

- Removing PHP8.1 deprecations - @Crell
- Removing PHP7.2 support

1.1.1 (2021-09-08)
------------------

Bug fixes:

- Prevent registering #[ParamProviders(...)] multiple times when benchmark methods are inherited #918 - @ocramius

1.1.0 (2021-08-15)
------------------

Bug fixes:

- Ensure all refs are passed to report in run mode #864
- Memory formatting respects precision directive #892
- Remove double title output when description used in console reports #848

Features:

- Allow env vars to be passed to the benchmark process.
- Allow config files to include other config files via. `$include` and
  `$include-glob` #989
- Added `contains` function to see if a value exists in a list.
- Added `frame` function to create a new data frame within an expression.
- Added `sum` and `count` functions #865
- New component based report generator #851
- HTML Bar Chart component #853
- Console Bar Chart component #858
- Data Frame and Expression Filtering #831
- Allow multiple benchmark paths to be specified from CLI #834
- Functions which require at least one value return NULL when values are
  empty #835
- Add `--limit` option to `log` command #879
- Add `bare-vertical` report configuration (same as `--report='extends: bare,{"vertical": true}`) #879

Improvement:

- Surpress reports if errors were encountered during the run #912
- Support expressions in parttion specifications
- Data can be accessed on any expression value (not just "parameters")
- Use automatic time unit for expression report #838
- Parameter handling refactored to be "safe": objects will not be unserialized
  in the PHPBench process #845
- Allow single quoted strings in expressions (better with JSON) #895

Other changes:

- "0" is not longer shown as the "set name" in reports, it is now an empty
  string.
- Lists and data frames can no longer be compared. Use the `frame` function to
  convert a list to a data frame (in the unlikely event you compare a list
  with a frame in a report).

1.0.4 (2021-07-18)
------------------

Bug fix:

- `runner.executor` setting is ineffective and related bugs #880

1.0.3 (2021-07-03)
------------------

Bug fix:

- Show warning if file is parsed but it is not a benchmark file. #883

  Files that are not suffixed with `Bench.php` are are reflected and their
  docblocks are parsed. Causing unexpected errors if unknown docblock tags are
  present.

  As changing this behavior (introduced by error in 2016) is a B/C break,
  it will not be changed in a bug-fix release.

  An option `runner.file_pattern` has been added however to enable the
  warnings to be resolved.

Improvement:

- Show warning if metadata could not be loaded for benchmark instead of
  an exception.

1.0.2 (2021-05-28)
------------------

Bug fix:

- Fix incorrect benchmark column definition in report #840
- Fix `--ansi` flag not be propagated to report output #844

1.0.1 (2021-05-11)
------------------

Bug fix:

- Error with bare report when DateTime used as param #832

1.0.0 (2021-05-09)
------------------

Improvements:

- Optionaly support for binary data in param providers #532
- Support serializable objects in param providers #823

Bug fix:

- Fix regression which requires phpbench to be installed with composer 2 #822

1.0.0-beta2
-----------

B/C breaks:

- Progress logger: startSuite now additionally accepts `RunnerConfig`

Improvements:

- Use package versions to show PHPBench version if not PHAR

Bug fixes:

- Unterminated XML reference #818 - @staabm
- Parent directory for custom script path not created #739 - @alexandrajulius
- Windows newline is not understood in expression language #817 - @dantleech

1.0.0-beta1
-----------

B/C breaks:

- Removed `self-update` functionality (suggest using `phive` instead(.
- Most configuration option names have changed. All options are now prefixed
  by their extension name, e.g. `bootstrap` => `runner.bootstrap`, `path` =>
  `runner.path`, `extensions` => `core.extensions`. See the configuration
  [documentation(https://phpbench.readthedocs.io/en/latest/configuration.html)
  for a full reference.
- Removed `time_unit` and `time_mode` configuration settings, as they are
  replaced by `runner.time_unit` and `runner.time_mode`.
- Environment provider `baseline` renamed to `sampler` to avoid
  concept-conflict with the runner baselines.

Improvements:

- Removed "summary" line from default progress output.
- Automatically detect time or memory units by default, added meta-units
  `time` and `memory`
- Unconditionally enable `xdebug` extension (previously the entire extension
  was hidden if Xdebug wasn't installed)

1.0.0-alpha9
------------

B/C Breaks:

- Extensions grouping related functionalities have been extracted from the
  ``CoreExtension``, this will change the location of some constants used
  (e.g. ``CoreExtension::TAG_PROGRESS_LOGGER`` is now
  ``RunnerExtension::PROGRESS_LOGGER``.
- Renamed `travis` progress logger to `plain`
- Removed awareness of `CONTINUOUS_INTEGRATION` environment variable

Features:

- Added `--working-dir` option
- Option to include the baseline rows in the `expression` report.
- Progress output is sent to STDERR, report output to STDOUT (enable you to
  pipe the output)
- Allow `--theme=` selection and configuration.
- Allow benchmarks to be configued in the config (`runner.{iterations,revs,time_unit,mode,etc}`)
- Include collected environmental information in the report data #789
- Allow providers to be enabled/disabled via. `env.enabled_providers` #789
- Support `@RetryThreshold` annotation, attribute, and
  `runner.retry_threshold` configuration.

Improvements:

- "local" executor will include non-existing benchmark classes and bootstrap
- Configuation options have generated documentation
- Preserve types in env information
- Make default true color theme compatible with light backgrounds.
- Added `vertical` layout to `bare` report (`vertical: true`).
- Removed `best` and `worst` columns by default from default report.
- Default to showing all columns in expression report
- Standard deviation in `default` report is shown as time
- Relative SD is color gradiated
- Trunacte long syntax error messages

Other:

- Automatically sign PHAR on release

1.0.0-alpha8
------------

BC Breaks:

- Removed `table` report generator, it is replaced by the `expression`
  generator which is now used to produce the `default` and `aggregate`
  reports. The output and configuration is largely the same, but some features
  have been removed.
- `html` and `markdown` output formats have been removed temporarily.

Features:

- Introduced `bare` report generator - provides all raw available report data
- Introduced `display_as_time` function to handle formatting time with
  throughput.
- Null `coalesce` function introduced in expression language

Improvements:

- Dynamically resolve timeunit / precision from expression (progress/report) #775
- Support specificaion of display-as precision in expression language
- Allow the display unit to be evaluated (for dynamically determining the unit based on the subject's preference)
- Make the display unit a node - allowing it to be pretty printed.
- Improved memory formatting (thousands separator, use abbreviated suffix)

1.0.0-alpha7
------------

- Support true color expression rendering #767
- Added `expression` report generator - will eventually replace the `table `report used
  for `aggregate` and `default `reports.
- Added `--format` to customize the summary shown in progress loggers
- String concatenation for expression language
- Show debug details (process spawning) with `-vvv`
- Support Xdebug 3

Bug fixes:

- @OutputTimeUnit doesn't propagate to default expression time unit #766

1.0.0-alpha6
------------

- Support for PHP 8 Attributes

1.0.0-alpha5
------------

Backward compatiblity breaks:

- `--uuid` renamed to `--ref` and `tag:` prefix removed #740
- No warnings - if assertion fails within tolerance zone then it is OK
- Assertion DSL has been
  [replaced](https://phpbench.readthedocs.io/en/latest/assertions.html) (only applicable vs. previous alpha
  versions)

Features:

- New [Expression Lanaugage](https://phpbench.readthedocs.io/en/latest/expression.html)

Improvements:

- Show difference to baseline in progress loggers.
- Highlight assertion failures.

1.0.0-alpha-4
-------------

Bug fixes:

- Numeric tags cause an error #717
- Benchmark errors cause reports to error
- Undefined console formatter `subtitle` #729
- Missing formatters not defined in correct place #727

Improvements:

- Colourful indication of success/failure/warnings when assertions are used.
- Allow multiple paths to be specified in config
- Add type restrictions to config values

1.0.0-alpha-3
-------------

Backward compatiblity breaks:

- `BenchmarkExecutorInterface#execute()` must now return an `ExecutionResults`
  object.
- `TemplateExecutor`: expect an `array` for the time measurement result instead
  of an `int`.
- Extensions use the Symfony `OptionsResolver` instead of provding an array of
  default values (which is in line with how other parts of PHPBench are
  working).
- Executors accept a single, immutable `ExecutionContext` instead of the
  mutable `SubjectMetadata` and `Iteration`
- Renamed the `microtime` executor to `remote`.
- `OutputInterface` is injected from the DI conatiner, `OutputAwareInterface`
  has been removed.

Features:

- Introduced `remote_script_remove` and `remote_script_path` options to assist
  in debugging.
- Added `local` executor - execute benchmarks with in the same process as
  PHPBench.

Improvements:

- Decorator added to improve error reporting for method executors.
- Benchmarks executed as they are found (no eager metadata loading)
- Allow direct reference to services (e.g. `--executor=debug` without need for
  a `debug` configuration).

1.0.0-alpha-2
-------------

- PHP 8.0 compatibility

1.0.0-alpha-1
-------------

Backward compatibility breaks:

- DBAL extension removed.
- PHPBench Reports extension removed.
- Removed Xdebug Trace integration
- Removed `--query` featre (only worked with DBAL, too complex).
- Removed `--context` (depreacted in favor of `--tag`).
- Removed `archive` and `delete` commands.
- Assertions now accept a single expression rather than a set of
  configuration options.
- Type hints have been added in most places - possibly causing issues with
  any extensions.
- Assets (storage, xdebug profiles) are now placed in `.phpbench`
- Services referenced via. fully qualified class names instead of strings.

Features:

- Configuration profiles
- Xdebug extension is loaded by default if extension is loaded
- Baseline: Specify baseline suite when running benchmarks and show differences in
  reports #648
- Assert against the baseline
- Show PHP version and the status of Xdebug and Opcache in the runner output
  #649
- Add `@Timeout` to enable a timeout to be specified for any given benchmark -
  @dantleech #614

Improvements

 - All assets now placed in `.phpbench` directory in CWD (instead of
   `./_storage` and `./xdebug`
 - `--tag` implicitly stores the benchmark (no need to additionally use
   `--store`)
 - Decrease benchmark overhead by rendering parameters in-template -
   @marc-mabe

Bugfixes:

 - Use `text` instead of `string` for envrionment key for DBAL storage - @flobee
 - Numeric tags are not found.

0.17.0
------

- Support for Symfony 5
- Dropped support for Symfony < 4.2
- Minimum version of PHP is 7.2

0.16.10
-------

Bug fix:

- Fix PHP 7.4 bug for baseline script (@julien-boudry)

0.16.0
------

BC Break:

- The ExecutorInterface has been
  removed and replaced by the `BenchmarkExecutorInterface`,
  `MethodExecutorInterface` and `HealthCheckInterface`.
- The Executor namespace has been moved from `PhpBench\Benchmark\Executor` to
  `PhpBench\Executor`.

Features:

- Support for named parameters #574
  - Replaces `params` column in reports with `set` (showing param set name) by
    default
  - Progress loggers show param name.
  - Serialized XML documents have a new element `parameter-set` to contain
    parameter elements.

Improvements:

- Various CI and code quality fixes, thanks @localheinz
- `groups` column no longer shown by default in reports.
- HTML report changed from XHTML to HTML5.
- Changed PHPStan level from 1 to 4.

0.15.0
------

- Minimumum supported PHP version is 7.1
- Renamed Factory => MetadataFactory
- Replace Style CI with PHP-CS-Fixer, fixes #537
- Allow any callable as a parameter provider. Fixes #533
- Remove benchmark dependency on JSON extension, use `serialize` instead.
  Fixes #534
- Allow Executor to be specified per benchmark/subject.
- Allow `@ParamProviders` to return a `Generator`. Fixes #529.
- Fix computation exception with `--stop-on-error` and multiple variants #563

0.14.0 Munich
-------------

### Features

- Assertions [docs](http://phpbench.readthedocs.io/en/latest/writing-benchmarks.html#assertions)
- Added `--disable-php-ini` option
- Report if opcode extension is enabled in env.
- Show enabled PHP extensions in env.
- PHP 7 only

### Bugfixes

- Fixed merging of PHP ini config
- Fixed Blackfire integration (#480 @torinaki )

### Improvements

- Internal refactoring!
- Aggregate report: `diff` column now shows multiplier (`x` times slower).
- Various travis imporvements @localheinz 
- Various CS fixes @villfa @Nyholm 
- Microtimer optimization @marc-mabe 
- Symfony 4 support @lcobucci 

0.13.0 Mali Mrack
-----------------

- Bumped minimum requirement to PHP 5.6
- Allow custom subject pattern #449
- No exception for empty file.
- Allow failure on HHVM due to https://github.com/lstrojny/functional-php/issues/114
- Prevent division by zero #451 
- Use non-logarithmic scale for diff column #445

0.12.0 Split
------------

- Column labels
- Non-strict JSON parser (e.g. `--report='extends: aggregate, cols: [ benchmark ]'`
- Dropped JSON schema, introduced Symfony Options Resolver.
- Show better exception message when beforeMethod is static
- Stop on error `run --stop-on-error`
- Allow additional extension autoloader to be configured
- Allow configuration of launcher.
- Refactored annotation reader (allow namespaced annotations, possibility to add benchmark annotations to PHPUnit tests).
- Initial Xdebug function trace support
- Container split into [independent library](https://github.com/phpbench/container)
- Fix skipping benchmarks
