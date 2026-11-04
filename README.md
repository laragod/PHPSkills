# PHPSkills

[![Latest Version](https://img.shields.io/packagist/v/laragod/phpskills)](https://packagist.org/packages/laragod/phpskills)
[![Total Downloads](https://img.shields.io/packagist/dt/laragod/phpskills)](https://packagist.org/packages/laragod/phpskills/stats)
[![Monthly Downloads](https://img.shields.io/packagist/dm/laragod/phpskills)](https://packagist.org/packages/laragod/phpskills/stats)
[![CI](https://github.com/laragod/PHPSkills/actions/workflows/ci.yml/badge.svg)](https://github.com/laragod/PHPSkills/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/badge/coverage-97%25-brightgreen)](https://github.com/laragod/PHPSkills/actions/workflows/ci.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209%20%2B%20strict-brightgreen)](phpstan.neon)
[![PHP Version](https://img.shields.io/packagist/dependency-v/laragod/phpskills/php?label=php)](composer.json)
[![License](https://img.shields.io/packagist/l/laragod/phpskills)](LICENSE)

**TrueSkill and Elo rating calculators for PHP 8.2+.**

A typed, tested port of Microsoft Research's [TrueSkill](https://www.microsoft.com/en-us/research/project/trueskill-ranking-system/)
ranking system, based on Jeff Moser's [Moserware.Skills](https://github.com/moserware/Skills) and his article
[Computing Your Skill](http://www.moserware.com/2010/03/computing-your-skill.html).

- Rate 1-vs-1 games, team games and free-for-alls with **any number of teams and players**, including draws.
- **Partial play** (a player only took part in half the match) and **partial updates**.
- **Match quality**: how balanced a game would be *before* it is played, for matchmaking.
- FIDE (chess) and Gaussian **Elo** calculators for comparison.
- Results match the reference F# implementation and Microsoft's online calculator (see the test suite).
- PHPStan level 9 with strict rules, 97% line coverage, CI on PHP 8.2, 8.3, 8.4 and 8.5, no runtime dependencies.

## Installation

```bash
composer require laragod/phpskills
```

## Quick start

### A 1-vs-1 game

```php
use Laragod\Skills\GameInfo;
use Laragod\Skills\Player;
use Laragod\Skills\Team;
use Laragod\Skills\TrueSkill\FactorGraphTrueSkillCalculator;

$gameInfo = new GameInfo();                     // TrueSkill defaults: mean 25, standard deviation 25/3
$calculator = new FactorGraphTrueSkillCalculator();

$alice = new Player('alice');                   // any int|string id, e.g. a database id
$bob = new Player('bob');

$teams = [
    new Team($alice, $gameInfo->getDefaultRating()),
    new Team($bob, $gameInfo->getDefaultRating()),
];

// Ranks: 1 is first place. Alice won.
$newRatings = $calculator->calculateNewRatings($gameInfo, $teams, [1, 2]);

$aliceRating = $newRatings->getRating($alice);
$aliceRating->getMean();                // 29.396
$aliceRating->getStandardDeviation();   // 7.171
$aliceRating->getConservativeRating();  // 7.881 = mean - 3 * sd, a good number to sort a leaderboard by
```

Store the mean and standard deviation per player, and build a `Rating` from them before the next game:

```php
use Laragod\Skills\Rating;

$team = new Team($alice, new Rating(mean: 29.396, standardDeviation: 7.171));
```

### Teams

```php
$red = (new Team())
    ->addPlayer(new Player('alice'), new Rating(28.0, 6.5))
    ->addPlayer(new Player('bob'), new Rating(22.0, 7.0));

$blue = (new Team())
    ->addPlayer(new Player('carol'), new Rating(31.0, 4.1))
    ->addPlayer(new Player('dave'), new Rating(19.5, 8.3));

$newRatings = $calculator->calculateNewRatings($gameInfo, [$red, $blue], [1, 2]);

foreach ($newRatings as $player => $rating) {
    echo "{$player}: {$rating}\n";
}

// alice: mean=30.2850, standardDeviation=6.1255
// bob:   mean=24.6501, standardDeviation=6.5298
// carol: mean=30.0906, standardDeviation=4.0084
// dave:  mean=15.7744, standardDeviation=7.5039
```

### Free-for-all and draws

Any number of teams works. Repeat a rank to record a draw:

```php
// Four solo players: carol won, alice and bob drew for second, dave came last
$newRatings = $calculator->calculateNewRatings(
    $gameInfo,
    [$teamAlice, $teamBob, $teamCarol, $teamDave],
    [2, 2, 1, 4],
);
```

Teams can be passed in any order; the ranks decide the outcome.

### Match quality (matchmaking)

The probability that the given teams would draw, from `0.0` (one side is going to be crushed)
to `1.0` (perfectly balanced):

```php
$quality = $calculator->calculateMatchQuality($gameInfo, [$red, $blue]);   // 0.531 for the teams above
```

### Partial play

If a player only took part in some of the match, say so and the update is weighted accordingly:

```php
$eve = new Player('eve', partialPlayPercentage: 0.5);
```

`partialUpdatePercentage` works the same way for the size of the rating update a player receives.

### Elo

```php
use Laragod\Skills\Elo\EloRating;
use Laragod\Skills\Elo\FideEloCalculator;
use Laragod\Skills\PairwiseComparison;

$chess = new GameInfo(initialMean: 1200, initialStandardDeviation: 0, beta: 200);
$calculator = FideEloCalculator::createWithDefaultKFactor();   // or createWithProvisionalKFactor()

$newRatings = $calculator->calculateNewRatings($chess, [
    new Team($white, new EloRating(2600)),
    new Team($black, new EloRating(2500)),
], PairwiseComparison::WIN->toRanks());   // white won

$newRatings->getRating($white)->getMean();   // 2603.6
```

`GaussianEloCalculator` uses the Gaussian win probability from the TrueSkill paper instead of the logistic curve.

## Choosing a calculator

| Calculator | Teams | Players per team | Partial play | Notes |
|---|---|---|---|---|
| `TrueSkill\FactorGraphTrueSkillCalculator` | 2 or more | 1 or more | yes | The full algorithm. Use this unless you have a reason not to. |
| `TrueSkill\TwoTeamTrueSkillCalculator` | exactly 2 | 1 or more | no | Closed-form two-team update, a bit faster. |
| `TrueSkill\TwoPlayerTrueSkillCalculator` | exactly 2 | exactly 1 | no | The textbook 1-vs-1 update. |
| `Elo\FideEloCalculator` | exactly 2 | exactly 1 | no | Classic chess Elo with FIDE K-factors. |
| `Elo\GaussianEloCalculator` | exactly 2 | exactly 1 | no | Elo with a Gaussian win probability. |

All calculators share the `SkillCalculator` API: `calculateNewRatings()`, `calculateMatchQuality()` and `isSupported()`.
Passing an unsupported number of teams or players throws an `InvalidArgumentException`.

## Game parameters

`GameInfo` holds the tunables of the TrueSkill model. The defaults are the ones from the paper:

| Parameter | Default | Meaning |
|---|---|---|
| `initialMean` | 25 | Mean (mu) of a new player. |
| `initialStandardDeviation` | 25/3 | Standard deviation (sigma) of a new player. |
| `beta` | 25/6 | The skill distance that gives roughly an 80% win chance. Smaller = more deterministic game. |
| `dynamicsFactor` | 25/300 | Additive uncertainty (tau) per game, so ratings never freeze completely. |
| `drawProbability` | 0.10 | How often games are drawn. Set to `0.0` if your game has no draws. |

```php
$gameInfo = new GameInfo(drawProbability: 0.0);
```

## Upgrading from 1.x

Version 2 keeps the same classes and the same numbers, but is a rewrite of everything around them.
See the [CHANGELOG](CHANGELOG.md) for the full list. The changes most likely to affect you:

- PHP 8.2 or newer is required.
- `PairwiseComparison` is an `enum` (`PairwiseComparison::WIN`, `::DRAW`, `::LOSE`).
- Everything is typed: numbers are `float`, ranks are `int`, player ids are `int|string`.
- Elo calculators take `Team` objects, exactly like the TrueSkill calculators.
- Bad arguments throw `InvalidArgumentException`; asking a team for a player it does not have throws `OutOfBoundsException`.
- `Rating::getPartialUpdate()` is static, `GaussianDistribution::subtract()` is gone (use `absoluteDifference()`).

## Development

```bash
composer install
composer check          # Pint + PHPStan (level 9, strict rules) + PHPUnit
composer test:coverage  # needs Xdebug or PCOV
composer refactor       # Rector dry run
```

## Credits

- [Ralf Herbrich, Tom Minka and Thore Graepel](https://www.microsoft.com/en-us/research/publication/trueskilltm-a-bayesian-skill-rating-system/) at Microsoft Research designed TrueSkill.
- [Jeff Moser](http://www.moserware.com/) wrote Moserware.Skills and the math paper this port follows.
- Alexander Liljengård wrote the original PHP port.
- Maintained since 2024 by [Filip Cieślik](https://laragod.com) (Laragod).

## License

BSD 2-Clause, the same as the original Moserware.Skills. See [LICENSE](LICENSE).
