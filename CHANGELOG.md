CHANGELOG
=========

develop
-------

- Renamed Factory => MetadataFactory
- Replace Style CI with PHP-CS-Fixer, fixes #537
- Allow any callable as a parameter provider. Fixes #533
- Remove benchmark dependency on JSON extension, use `serialize` instead.
  Fixes #534
- Allow Executor to be specified per benchmark/subject.
- Allow `@ParamProviders` to return a `Generator`. Fixes #529.

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
