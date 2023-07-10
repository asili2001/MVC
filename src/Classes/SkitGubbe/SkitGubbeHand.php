<?php

namespace App\Classes\SkitGubbe;

use App\Classes\Cards\CardHand;
use App\Classes\Cards\Card;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * SkitGubbeHand is a type of CardHand Class thathas the
 * capability of tallying up points according to the
 * specific regulations of the game of SkitGubbe.
 */
class SkitGubbeHand extends CardHand
{
    /**
     * @var array<int|string> $allowedNames
    */
    protected array $allowedNames = ["joker", "A", 2, 3, 4, 5, 6, 7, 8, 9, 10, "J", "Q", "K"];

    /**
     * 100 represents the card 2
     * 200 represents the card 10
     * @var array<int|array<int>> $points
    */
    private array $points = [
        "A" => 11,
        "2" => 100,
        "3" => 1,
        "4" => 2,
        "5" => 3,
        "6" => 4,
        "7" => 5,
        "8" => 6,
        "9" => 7,
        "10" => 200,
        "J" => 8,
        "Q" => 9,
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

    public static function cardPoints(Card $card): int|null
    {
        $hand = new SkitGubbeHand();
        $res = intval($hand->points[$card->getName()]);
        return $res === 0 ? null : $res;
    }

    /**
     * returns the smallets card compare to the input card
     *
     * @return Card The card.
    */
    public function getNextBigger(Card $card): Card
    {
        $cardPoints = $this->cardPoints($card);
        $nextBigger = null;

        foreach ($this->getCardNames() as $value) {
            $number = $this->points[$value] ?? null;
            if ($number >= $cardPoints) {
                if ($nextBigger === null || $number < $nextBigger) {
                    $nextBigger = $number;
                }
            }
        }

        // if there is no bigger card.
        if (!$nextBigger) {
            return $this->getCards()[0];
        }

        $res = $this->getByName((string)array_search($nextBigger, $this->points));

        if (is_null($res)) {
            throw new Exception("Invalid Card");
        }

        return $res;
    }
    /**
     * returns the smallets card compare to the input card
     *
     * @return int The card index.
    */
    public function getNextBiggerIndex(Card $card): int
    {
        $cardPoints = $this->cardPoints($card);
        $nextBigger = null;

        foreach ($this->getCardNames() as $value) {
            $number = $this->points[$value] ?? null;
            if ($number >= $cardPoints) {
                if ($nextBigger === null || $number < $nextBigger) {
                    $nextBigger = $number;
                }
            }
        }

        // if there is no bigger card.
        if (!$nextBigger) {
            return 0;
        }

        $res = $this->getIndexByName((string)array_search($nextBigger, $this->points));

        if (is_null($res)) {
            throw new Exception("Invalid Card");
        }

        return $res;
    }
}
