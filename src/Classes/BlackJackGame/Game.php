<?php

namespace App\Classes\BlackJackGame;

use App\Classes\Cards\DeckOfCards;

/**
 * The Game class is the primary component or "core" of the blackjack game,
 * which contains all the necessary functions for the game to function properly.
 */
class Game
{
    /**
     * The game array
     * @var array<mixed> $gameArr
     */
    private $gameArr = [
        "dealerHand" => null,
        "playerHand" => null,
        "deck" => null,
        "winner" => null
    ];
    /**
     * This constructor method initializes the game state by
     * either using provided game data or generating a new
     * deck and dealing cards to the dealer and player.
     * @param array<mixed> $gameArr
     *
     *  $gameArr
     */
    public function __construct(array $gameArr)
    {
        if (count($gameArr) > 0) {
            $gameData = [
                "dealerHand" => $gameArr["dealerHand"],
                "playerHand" => $gameArr["playerHand"],
                "deck" => $gameArr["deck"],
                "winner" => $gameArr["winner"]
            ];
            $this->gameArr = $gameData;
            return;
        }

        $deck = new DeckOfCards();
        $deck->resetCards();
        $deck->shuffleCards();
        $deal = array_values($deck->dealCards(2, 4));
        // hide first dealer card
        array_values($deal[0]->getCards(0))[0]->hide();

        $dealerHand = new BlackJackHand($deal[0]->getCards());
        $playerHand = new BlackJackHand($deal[1]->getCards());

        $gameData = [
            "dealerHand" => $dealerHand,
            "playerHand" => $playerHand,
            "deck" => $deck,
            "winner" => null
        ];
        $this->gameArr = $gameData;
    }

    /**
     * The hit function will take of a card from the deck and add it to the Player Hand.
     * If the player got more points then 21. The winner object will be set to "dealer".
     */
    public function hit(): void
    {
        if (!is_null($this->gameArr["winner"])) {
            return;
        }

        /**
         * @var DeckOfCards $deck
         */
        $deck = new DeckOfCards($this->gameArr["deck"]->getCards());

        $this->gameArr["playerHand"]->addCard($deck->drawCard()[0]);

        if ($this->gameArr["playerHand"]->getPoints() > 21) {
            $this->gameArr["winner"] = "dealer";
        }
    }
    /**
     * The hit function will give the dealer cards until the total of points will be 17 or above.
     * If the dealer have more points then 21 or the dealer have less points the n the player. the player winns.
     * The same thing applies to the Player.
     * If both dealer and player got same amount of points, the result will me "push". (no one winns).
     */
    public function stand(): void
    {
        if ($this->gameArr["winner"]) {
            return;
        }

        // unhide first card
        $this->gameArr["dealerHand"]->getCards()[0]->unhide();

        $dealerPoints = $this->gameArr["dealerHand"]->getPoints();
        $playerPoints = $this->gameArr["playerHand"]->getPoints();

        /**
         * @var DeckOfCards $deck
         */
        $deck = new DeckOfCards($this->gameArr["deck"]->getCards());

        // dealer draws cards until the points equal to 17 or over 17
        while ($dealerPoints < 17) {
            $this->gameArr["dealerHand"]->addCard($deck->drawCard()[0]);
            $dealerPoints = $this->gameArr["dealerHand"]->getPoints();
        }

        if ($dealerPoints > 21) {
            $this->gameArr["winner"] = "player";
        } elseif ($playerPoints > 21) {
            $this->gameArr["winner"] = "dealer";
        } elseif ($playerPoints > $dealerPoints) {
            $this->gameArr["winner"] = "player";
        } elseif ($playerPoints < $dealerPoints) {
            $this->gameArr["winner"] = "dealer";
        } elseif ($playerPoints === $dealerPoints) {
            $this->gameArr["winner"] = "push";
        }
        $this->gameArr["deck"] = $deck;
    }

    /**
     * The getGameDeata will return the $gameArr variable
     * @return array<mixed> $gameArr
     */
    public function getGameData(): array
    {
        return $this->gameArr;
    }
}
