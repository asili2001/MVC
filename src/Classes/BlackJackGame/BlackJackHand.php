<?php

namespace App\Classes\BlackJackGame;

use App\Classes\Cards\CardHand;
use App\Classes\Cards\Card;

/**
 * BlackJackHand is a type of CardHand Class thathas the
 * capability of tallying up points according to the
 * specific regulations of the game of blackjack.
 */
class BlackJackHand extends CardHand
{
    /**
     * @var array<int|string> $allowedNames
    */
    protected array $allowedNames = ["joker", "A", 2, 3, 4, 5, 6, 7, 8, 9, 10, "J", "Q", "K"];

    /**
     * @var array<int|array<int>> $points
    */
    private array $points = [
        "A" => [11, 1],
        "2" => 2,
        "3" => 3,
        "4" => 4,
        "5" => 5,
        "6" => 6,
        "7" => 7,
        "8" => 8,
        "9" => 9,
        "10" => 10,
        "J" => 10,
        "Q" => 10,
        "K" => 10
    ];
    /**
     * Creates a hand using the CardHand class
     * @param array<Card> $cards
    */
    public function __construct(array $cards = [])
    {
        parent::__construct($cards);
    }

    /**
     * calculates the points of cards in hand.
     *
     * @return int The points.
    */
    public function getPoints(): int
    {
        /**
         * @var int $points
        */
        $points = 0;
        $ones = 0;

        foreach ($this->getCards() as $card) {
            if (!$card->isHidden()) {
                $ones = ($card->getName() === "A") ? $ones + 1 : $ones;
                $points += ($card->getName() === "A") ? 0 : $this->points[strval($card->getName())];
            }
        }
        if (is_array($this->points["A"])) {
            for ($i = 0; $i < $ones; $i++) {
                $points += (($points + $this->points["A"][0]) > 21 ? $this->points["A"][1] : $this->points["A"][0]);
            }
        }
        return $points;
    }
}
