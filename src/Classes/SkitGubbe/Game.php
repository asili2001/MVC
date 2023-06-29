<?php

namespace App\Classes\SkitGubbe;

use App\Classes\Cards\Card;
use App\Classes\Cards\CardHand;
use App\Classes\Cards\DeckOfCards;

/**
 * The Game class is the primary component or "core" of the SkitGubbe game,
 * which contains all the necessary functions for the game to function properly.
 */
class Game
{
    /**
     * The game array
     * @var array<mixed> $gameArr
     */
    private $gameArr = [
        "computerHand" => null,
        "playerHand" => null,
        "computerVisibleCards" => null,
        "computerHiddenCards" => null,
        "playerVisibleCards" => null,
        "playerHiddenCards" => null,
        "floor" => null,
        "basket" => null,
        "deck" => null,
        "message" => "",
        "isWinner" => null
    ];

    /**
     * This constructor method initializes the gameArr state
     * @param array<mixed> $gameArr
     * @param int $deleteCards
    */
    public function __construct(array $gameArr, int $deleteCards = 0)
    {
        if (count($gameArr) > 0) {
            $gameData = [
                "computerHand" => $gameArr["computerHand"],
                "playerHand" => $gameArr["playerHand"],
                "computerVisibleCards" => $gameArr["computerVisibleCards"],
                "computerHiddenCards" => $gameArr["computerHiddenCards"],
                "playerVisibleCards" => $gameArr["playerVisibleCards"],
                "playerHiddenCards" => $gameArr["playerHiddenCards"],
                "floor" => $gameArr["floor"],
                "basket" => $gameArr["basket"],
                "deck" => $gameArr["deck"],
                "message" => $gameArr["message"],
                "isWinner" => $gameArr["isWinner"]
            ];
            $this->gameArr = $gameData;
            return;
        }

        $deck = new DeckOfCards();
        $deck->resetCards();
        $deck->shuffleCards();
        if ($deleteCards > 0) {
            $deck->dealCards(1, $deleteCards);
        }
        $playersDeal = array_values($deck->dealCards(2, 6));
        $HiddenCardsDeal = array_values($deck->dealCards(2, 6));
        $visibleCardsDeal = array_values($deck->dealCards(2, 6));

        // hide the Hidden cards
        foreach($HiddenCardsDeal as $cards) {
            $cards = $this->hideCards($cards);
        }

        $computerHand = new SkitGubbeHand($playersDeal[0]->getCards());
        $playerHand = new SkitGubbeHand($playersDeal[1]->getCards());

        $gameData = [
            "computerHand" => $computerHand,
            "playerHand" => $playerHand,
            "floor" => new SkitGubbeHand([]),
            "basket" => new SkitGubbeHand([]),
            "deck" => $deck,
            "message" => "",
            "computerVisibleCards" => $visibleCardsDeal[0],
            "computerHiddenCards" => $HiddenCardsDeal[0],
            "playerVisibleCards" => $visibleCardsDeal[1],
            "playerHiddenCards" => $HiddenCardsDeal[1],
            "isWinner" => null
        ];
        $this->gameArr = $gameData;
    }

    /**
     * The getGameData will return the $gameArr variable
     * @return array<mixed> $gameArr
     */
    public function getGameData(): array
    {
        return $this->gameArr;
    }

    public function discard(string $hand, int $cardIndex, bool $fill = true): string
    {
        $gameData = $this->gameArr;
        
        /**
         * @var cardHand $playerHand
         */
        $playerHand = $gameData[$hand];

        /**
         * @var DeckOfCards $deck
         */
        $deck = $gameData["deck"];

        /**
         * @var Card $discardedCard
         */
        $discardedCard = $playerHand->drawCard($cardIndex);
        $addToFloorRes = $this->addToFloor($discardedCard, $hand);
        if ($fill && count($deck->getCards()) >= 1) {
            for ($i = count($playerHand->getCardNames()); $i < 3; $i++) {
                $playerHand->addCard($deck->drawCard()[0]);
            }
        }


        return $addToFloorRes;

    }

    public function cardExists(string $hand, int $cardIndex): bool
    {
        $gameData = $this->gameArr;

        /**
         * @var cardHand $playerHand
         */
        $playerHand = $gameData[$hand];

        $exists = true;

        try {
            $playerHand->getCards($cardIndex);
        } catch (\Throwable $th) {
            $exists = false;
        }

        return $exists;

    }

    public function addToFloor(Card $card, string $player): string
    {
        /**
         * @var cardHand $floor
         */
        $floor = $this->gameArr["floor"];

        /**
         * @var cardHand $basket
         */
        $basket = $this->gameArr["basket"];

        /**
         * @var cardHand $playerHand
         */
        $playerHand = $this->gameArr[$player];

        $floor->addCard($card);
        $floorCards = $floor->getCards();

        // check if card is 2. send a response of "ANOTHER_CARD".
        if ($floorCards[count($floorCards) - 1]->getName() == 2) {
            return "ANOTHER_CARD";
        }

        // check if card is 10. move the cards from floor to basket
        if ($floorCards[count($floorCards) - 1]->getName() == 10) {
            foreach ($floorCards as $floorCard) {
                $basket->addCard($floorCard);
            }
            $this->gameArr["floor"] = new SkitGubbeHand([]);
            return "CLEAR_FLOOR";
        }

        // check if the card is not equal or bigger then the last floor card
        $tmpHand = new SkitGubbeHand();
        if (
            count($floorCards) >= 2 &&
            ($floorCards[count($floorCards) - 2]->getName() != "10" &&
            $floorCards[count($floorCards) - 2]->getName() != "2") &&
            $tmpHand->cardPoints($floorCards[count($floorCards) - 1]) < $tmpHand->cardPoints($floorCards[count($floorCards) - 2])
        ) {
            foreach ($floorCards as $floorCard) {
                $playerHand->addCard($floorCard);
            }
            $this->gameArr["floor"] = new SkitGubbeHand([]);
            return "WEAK_CARD";
        }

        // if last 4 cards has same name. move the cards from floor to basket
        $lastSameCards = count($floorCards) >= 4 &&
            ($floorCards[count($floorCards) - 1]->getName() === $floorCards[count($floorCards) - 2]->getName()) &&
            ($floorCards[count($floorCards) - 2]->getName() === $floorCards[count($floorCards) - 3]->getName()) &&
            ($floorCards[count($floorCards) - 3]->getName() === $floorCards[count($floorCards) - 4]->getName());

        if ($lastSameCards) {
            foreach ($floorCards as $floorCard) {
                $basket->addCard($floorCard);
            }
            $this->gameArr["floor"] = new SkitGubbeHand([]);
            return "CLEAR_FLOOR";
        };

        return "ADDED_TO_FLOOR";
    }

    public function usePlayerFloor(string $player, int $index, bool $fromVisible = true): void
    {

        /**
         * @var cardHand $playerHand
         */
        $playerHand = $this->gameArr[$player];
        $from = null;



        if ($player === "computerHand") {
            $from = "computerVisibleCards";

            if (!$fromVisible) {
                $from = "computerHiddenCards";
            }

        }

        if ($player === "playerHand") {
            $from = "playerVisibleCards";

            if (!$fromVisible) {
                $from = "playerHiddenCards";
            }

        }

        /**
         * @var cardHand $playerFrom
         */
        $playerFrom = $this->gameArr[$from];

        $drawCard = $playerFrom->drawCard($index);

        if (gettype($drawCard) !== "array") {
            $playerHand->addCard($drawCard);
        }
    }

    /**
     * It will return 3 boolean values. the first will check if the player hand is empty from cards,
     * the second will check the visible cards and the last bool checks the hidden cards
     * @param string $player
     * @return array<bool>
     */
    public function availability(string $player): array
    {

        /**
         * @var cardHand $computerHand
         */
        $computerHand = $this->gameArr["computerHand"];
        /**
         * @var cardHand $computerVisibleCards
         */
        $computerVisibleCards = $this->gameArr["computerVisibleCards"];
        /**
         * @var cardHand $computerHiddenCards
         */
        $computerHiddenCards = $this->gameArr["computerHiddenCards"];
        /**
         * @var cardHand $playerHand
         */
        $playerHand = $this->gameArr["playerHand"];
        /**
         * @var cardHand $playerVisibleCards
         */
        $playerVisibleCards = $this->gameArr["playerVisibleCards"];
        /**
         * @var cardHand $playerHiddenCards
         */
        $playerHiddenCards = $this->gameArr["playerHiddenCards"];
        $res = [
            count($computerHand->getCardNames()) < 1,
            count($computerVisibleCards->getCardNames()) < 1,
            count($computerHiddenCards->getCardNames()) < 1
        ];

        if ($player === "player") {
            $res = [
                count($playerHand->getCardNames()) < 1,
                count($playerVisibleCards->getCardNames()) < 1,
                count($playerHiddenCards->getCardNames()) < 1
            ];
        }

        return $res;
    }

    public function checkWinner(): void
    {
        $playerAvailability = $this->availability("player");
        $computerAvailability = $this->availability("computer");

        if (empty(array_diff($playerAvailability, [true, true, true]))) {
            $this->gameArr["isWinner"] = true;
        }
        if (empty(array_diff($computerAvailability, [true, true, true]))) {
            $this->gameArr["isWinner"] = false;
        }
    }

    public function setMessage(string $message): void
    {
        $this->gameArr["message"] = $message;
    }

    public function fillHand(string $hand): void
    {
        $gameData = $this->gameArr;
        /**
         * @var cardHand $playerHand
         */
        $playerHand = $gameData[$hand];
        /**
         * @var DeckOfCards $deck
         */
        $deck = $gameData["deck"];
        // draw new card from deck
        if (count($deck->getCards()) >= 1) {
            for ($i=count($playerHand->getCardNames()); $i < 3; $i++) {
                $playerHand->addCard($deck->drawCard()[0]);
            }
        }
    }

    private function hideCards(SkitGubbeHand|CardHand $hand): CardHand
    {
        foreach ($hand->getCards() as $card) {
            $card->hide();
        }

        return $hand;
    }
}
