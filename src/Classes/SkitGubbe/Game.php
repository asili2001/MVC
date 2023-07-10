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
        $hiddenCardsDeal = array_values($deck->dealCards(2, 6));
        $visibleCardsDeal = array_values($deck->dealCards(2, 6));

        // hide the Hidden cards
        foreach($hiddenCardsDeal as $cards) {
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
            "computerHiddenCards" => $hiddenCardsDeal[0],
            "playerVisibleCards" => $visibleCardsDeal[1],
            "playerHiddenCards" => $hiddenCardsDeal[1],
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

    public function discard(string $hand, int $cardIndex, bool $fill): string
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
         * @var SkitGubbeHand $floor
         */
        $floor = $this->gameArr["floor"];

        /**
         * @var SkitGubbeHand $basket
         */
        $basket = $this->gameArr["basket"];

        /**
         * @var SkitGubbeHand $playerHand
         */
        $playerHand = $this->gameArr[$player];

        $floor->addCard($card);
        $floorCards = $floor->getCards();
        $lastCardIndex = count($floorCards) - 1;
        $lastCard = $floorCards[$lastCardIndex];

        if ($lastCard->getName() == 2) {
            return "ANOTHER_CARD";
        }

        if ($lastCard->getName() == 10) {
            $this->moveFloorToBasket($floorCards, $basket);
            return "CLEAR_FLOOR";
        }

        if ($this->isWeakCard($floorCards, $lastCardIndex)) {
            $this->moveFloorToPlayer($floorCards, $playerHand);
            return "WEAK_CARD";
        }

        if ($this->hasLastFourSameCards($floorCards, $lastCardIndex)) {
            $this->moveFloorToBasket($floorCards, $basket);
            return "CLEAR_FLOOR";
        }

        return "ADDED_TO_FLOOR";
    }

    /**
     * move all cards from floor to basket
     * @param array<Card> $floorCards
     * @param CardHand $basket
     */
    private function moveFloorToBasket(array $floorCards, CardHand $basket): void
    {
        foreach ($floorCards as $floorCard) {
            $basket->addCard($floorCard);
        }
        $this->gameArr["floor"] = new SkitGubbeHand([]);
    }

    /**
     * move all cards from floor to player hand
     * @param array<Card> $floorCards
     * @param CardHand $playerHand
     */
    private function moveFloorToPlayer(array $floorCards, cardHand $playerHand): void
    {
        foreach ($floorCards as $floorCard) {
            $playerHand->addCard($floorCard);
        }
        $this->gameArr["floor"] = new SkitGubbeHand([]);
    }

    /**
     * check if the card is weak
     * @param array<Card> $floorCards
     * @param int $lastCardIndex
     */
    private function isWeakCard(array $floorCards, int $lastCardIndex): bool
    {
        $tmpHand = new SkitGubbeHand();

        return (
            count($floorCards) >= 2 &&
            ($floorCards[$lastCardIndex - 1]->getName() != "10" &&
            $floorCards[$lastCardIndex - 1]->getName() != "2") &&
            $tmpHand->cardPoints($floorCards[$lastCardIndex]) < $tmpHand->cardPoints($floorCards[$lastCardIndex - 1])
        );
    }

    /**
     * check if the fllor have 4 of same cards
     * @param array<Card> $floorCards
     * @param int $lastCardIndex
     */
    private function hasLastFourSameCards(array $floorCards, int $lastCardIndex): bool
    {
        return (
            count($floorCards) >= 4 &&
            ($floorCards[$lastCardIndex]->getName() === $floorCards[$lastCardIndex - 1]->getName()) &&
            ($floorCards[$lastCardIndex - 1]->getName() === $floorCards[$lastCardIndex - 2]->getName()) &&
            ($floorCards[$lastCardIndex - 2]->getName() === $floorCards[$lastCardIndex - 3]->getName())
        );
    }



    public function usePlayerFloor(string $player, int $index, bool $fromVisible): void
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
        // $playerCardsCount = count($playerHand->getCardNames());
        if (count($deck->getCards()) >= 1) {
            for ($i = count($playerHand->getCardNames()); $i < 3; $i++) {
                $playerHand->addCard($deck->drawCard()[0]);
            
                if (count($deck->getCards()) < 1) {
                    break;
                }
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
