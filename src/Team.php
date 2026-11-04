<?php

declare(strict_types=1);

namespace Laragod\Skills;

use InvalidArgumentException;

/**
 * A team of one or more players and their ratings.
 */
class Team extends RatingContainer
{
    public function __construct(?Player $player = null, ?Rating $rating = null)
    {
        parent::__construct();

        if ($player instanceof Player) {
            if (! $rating instanceof Rating) {
                throw new InvalidArgumentException('A rating is required when a player is given');
            }

            $this->addPlayer($player, $rating);
        }
    }

    /**
     * @return $this
     */
    public function addPlayer(Player $player, Rating $rating): static
    {
        return $this->setRating($player, $rating);
    }
}
