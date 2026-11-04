<?php

declare(strict_types=1);

namespace Laragod\Skills;

use Countable;
use Generator;
use IteratorAggregate;

/**
 * Maps players to their ratings, preserving insertion order.
 *
 * @implements IteratorAggregate<Player, Rating>
 */
class RatingContainer implements Countable, IteratorAggregate
{
    /** @var HashMap<Player, Rating> */
    private readonly HashMap $playerToRating;

    public function __construct()
    {
        $this->playerToRating = new HashMap;
    }

    public function getRating(Player $player): Rating
    {
        return $this->playerToRating->getValue($player);
    }

    public function hasPlayer(Player $player): bool
    {
        return $this->playerToRating->hasKey($player);
    }

    /**
     * @return $this
     */
    public function setRating(Player $player, Rating $rating): static
    {
        $this->playerToRating->setValue($player, $rating);

        return $this;
    }

    /**
     * @return list<Player>
     */
    public function getAllPlayers(): array
    {
        return $this->playerToRating->getAllKeys();
    }

    /**
     * @return list<Rating>
     */
    public function getAllRatings(): array
    {
        return $this->playerToRating->getAllValues();
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return $this->playerToRating->count();
    }

    /**
     * @return Generator<Player, Rating>
     */
    public function getIterator(): Generator
    {
        $ratings = $this->getAllRatings();

        foreach ($this->getAllPlayers() as $index => $player) {
            yield $player => $ratings[$index];
        }
    }
}
