CHANGELOG
=========

develop
-------

Backward compatibility breaks:

- DBAL extension removed.
- PHPBench Reports extension removed.
- Removed `--query` featre (only worked with DBAL, too complex).
- Removed `--context` (depreacted in favor of `--tag`)

Features

- XDebug extension is loaded by default if extension is loaded
- Baseline: Specify baseline suite when running benchmarks and show differences in
  reports #648
- Show PHP version and the status of XDebug and Opcache in the runner output
  #649
- Add `@Timeout` to enable a timeout to be specified for any given benchmark -
  @dantleech #614

Improvements

 - `--tag` implicitly stores the benchmark (no need to additionally use
   `--store`)
 - Decrease benchmark overhead by rendering parameters in-template -
   @marc-mabe

Bugfixes:

 - Use `text` instead of `string` for envrionment key for DBAL storage - @flobee

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
- Initial XDebug function trace support
- Container split into [independent library](https://github.com/phpbench/container)
- Fix skipping benchmarks
