<?php

declare(strict_types=1);

namespace Laragod\Skills;

class RatingContainer
{
    private $_playerToRating;

    public function __construct()
    {
        $this->_playerToRating = new HashMap();
    }

    public function getRating(Player $player)
    {
        return $this->_playerToRating->getValue($player);
    }

    public function setRating(Player $player, Rating $rating): HashMap
    {
        return $this->_playerToRating->setValue($player, $rating);
    }

    public function getAllPlayers(): array
    {
        return $this->_playerToRating->getAllKeys();
    }

    public function getAllRatings(): array
    {
        return $this->_playerToRating->getAllValues();
    }

    public function count(): int
    {
        return $this->_playerToRating->count();
    }
}
