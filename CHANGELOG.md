CHANGELOG
=========

develop
-------

Backward compatiblity breaks:

- `--uuid` renamed to `--ref` and `tag:` prefix removed #740

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
