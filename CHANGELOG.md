# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-11-04

A ground-up modernisation of the 1.x code base for PHP 8.2 – 8.5. The algorithms
are unchanged and still produce the reference values from Ralf Herbrich's F#
implementation and Microsoft's online calculator; everything around them is new.

### Added

- Full type coverage: every property, parameter and return value is typed, the
  factor graph is generic (`Variable<T>`, `Factor<T>`, …) via PHPStan generics, and
  the code base passes PHPStan level 9 with `phpstan-strict-rules`.
- `PairwiseComparison` is a backed `enum` with a `toRanks()` helper.
- `Team` / `RatingContainer` implement `IteratorAggregate` (`foreach ($team as $player => $rating)`)
  and expose `hasPlayer()`.
- `GameInfo` validates the draw probability; calculators validate that the number
  of ranks matches the number of teams.
- `Range`, `PlayersRange` and `TeamsRange` are `Stringable`, which makes the
  validation error messages readable ("this calculator supports exactly 2 teams").
- Tests for `Player`, `Team`, `Rating`, `GameInfo`, `PairwiseComparison`, the
  Gaussian Elo calculator, calculator argument validation and numeric edge cases
  (67 tests, 97% line coverage).
- GitHub Actions CI on PHP 8.2, 8.3, 8.4 and 8.5 (Pint, PHPStan, PHPUnit) with a
  coverage upload, plus `composer check` to run everything locally.
- Rector configuration, `.editorconfig`, `.gitattributes` with export-ignores and
  a `CHANGELOG.md`.

### Changed

- **Requires PHP 8.2 or newer.** PHPUnit 11/12, PHPStan 2, Pint and Rector 2 as dev tools.
- Elo calculators (`FideEloCalculator`, `GaussianEloCalculator`) accept `Team`
  objects exactly like the TrueSkill calculators. The 1.x version read plain arrays
  and did not actually work.
- `RankSorter::sort()` is a stable sort and rejects mismatched array lengths.
- Argument problems throw `InvalidArgumentException` instead of a bare `Exception`;
  asking a team for a player it does not contain throws `OutOfBoundsException`.
- `Rating::getPartialUpdate()` is `static` (it never used instance state).
- `GaussianDistribution::cumulativeTo()` only takes `$x`; the mean and standard
  deviation parameters were silently ignored before.
- `GaussianDistribution` is immutable and `final`.
- `Matrix` throws `LogicException` for non-square determinants/adjugates and
  `InvalidArgumentException` for size mismatches; `SquareMatrix` rejects a
  non-square number of values.
- Factors and the variables they are bound to are stored side by side; the
  `HashMap` indirection inside factors is gone.
- The license file is `LICENSE` and `composer.json` declares `BSD-2-Clause`
  (the terms were always BSD, the metadata said otherwise).
- Internal `_underscore` property names and untyped signatures are gone.

### Removed

- `GaussianDistribution::subtract()` – use `absoluteDifference()`.
- `Guard::argumentNotNull()` – native types make it redundant.
- `FactorGraphs\DefaultVariable` – unused.
- `FactorGraph::setVariableFactory()` – the factory is passed to the constructor.
- `FactorGraphLayer::getOutputVariablesGroups()` no longer returns by reference;
  layers add groups through `addOutputVariablesGroup()`.
- `composer.lock` is no longer committed (this is a library).

## [1.0.4] and earlier

See the [GitHub releases](https://github.com/laragod/PHPSkills/releases) for the
history of the 1.x line, which was a lightly modernised copy of the original
PHP port of Moserware.Skills.

[2.0.0]: https://github.com/laragod/PHPSkills/compare/1.0.4...2.0.0
