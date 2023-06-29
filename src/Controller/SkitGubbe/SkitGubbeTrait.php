<?php

namespace App\Controller\SkitGubbe;

use App\Classes\Cards\Card;
use App\Classes\Cards\CardHand;
use App\Classes\Cards\DeckOfCards;
use App\Classes\SkitGubbe\SkitGubbeHand;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use App\Classes\SkitGubbe as SkitGubbeGame;
use App\Entity\Skitgubbe as dbRes;

trait SkitGubbeTrait
{
    private function gameInit(Request $request): void
    {
        $session = $request->getSession();
        /**
         * @var array<mixed> $gameSession
         */
        $gameSession = $session->get("skitgubbe", []);

        $this->skitGubbe = new SkitGubbeGame\Game($gameSession, 30);
        $this->skitGubbe->checkWinner();
        $session->set("skitgubbe", $this->skitGubbe->getGameData());
    }

    private function checkEndGame(bool $redirectToApi = true): void
    {
        $this->skitGubbe->checkWinner();

        $redirect = $redirectToApi ? "skitGubbePlayJson" : "skitGubbePlay";

        if (!is_null($this->skitGubbe->getGameData()["isWinner"])) {
            die($this->redirectToRoute($redirect));
        }
    }

    private function sessionSave(Request $request): void
    {
        $session = $request->getSession();
        $session->set("skitgubbe", $this->skitGubbe->getGameData());
    }

    /**
     * converts array of string numbers to array of int numbers
     * @param array<string> $items
     * @return array<int>
     */
    private function strToIntArr(array $items): array
    {
        $res = [];
        foreach ($items as $value) {
            array_push($res, intval(($value)));
        }

        return $res;
    }

    /**
     * checks if cards have same name by the indexes array
     * @param array<string> $cardIndexs
     * @return bool
     */
    private function identicalCardsCheck(array $cardIndexs): bool
    {
        $cardIndexs = $this->strToIntArr($cardIndexs);
        $cardsAvailability = $this->skitGubbe->availability("player");
        $hand = "playerHand";

        if ($cardsAvailability[0]) {
            $hand = "playerVisibleCards";

            if ($cardsAvailability[1]) {
                $hand = "playerHiddenCards";
            }
        }


        $gameData = $this->skitGubbe->getGameData();
        /**
         * @var CardHand $handObj
         */
        $handObj = $gameData[$hand];
        /**
         * @var DeckOfCards $deck
         */
        $deck = $gameData["deck"];
        $handCards = $handObj->getCards();

        $cardName = is_null($handCards[$cardIndexs[0]]->getName()) ? "" : $handCards[$cardIndexs[0]]->getName();

        $allSameAsFirst = $handObj->getAllByName($cardName, true);
        $cardNames = [];
        $cardNamesDiff = [];
        $deck = $deck->getCards();
        $identical = true;


        if (count($cardIndexs) > 1 && count($deck) >= count($cardIndexs)) {

            if (empty(array_intersect($allSameAsFirst, $cardIndexs))) {
                $identical = false;
            }
            for ($i=0; $i < count($handCards); $i++) {
                try {
                    array_push($cardNames, $handCards[$i]->getName());
                    array_push($cardNamesDiff, $handCards[$cardIndexs[0]]->getName());
                    array_push($cardNamesDiff, $handCards[$cardIndexs[1]]->getName());
                } catch (\Throwable $th) {
                    $this->skitGubbe->setMessage("Card Not Found");
                }
            }

        }

        return $identical;
    }

    /**
     * checks if all items in array is type of int
     * @param array<mixed> $array
     */
    private function checkIntArray(array $array): bool
    {
        $all_numeric = true;
        foreach ($array as $key) {
            if (!(is_numeric($key))) {
                $all_numeric = false;
                break;
            }
        }

        return $all_numeric;
    }


    private function playCardFromVisible(int $cardIndex): void
    {
        $fromVisible = true;
        $cardsAvailability = $this->skitGubbe->availability("player");

        if ($cardsAvailability[1]) {
            $fromVisible = false;
        }

        $this->skitGubbe->usePlayerFloor("playerHand", $cardIndex, $fromVisible);
    }

    private function playComputerTurn(): void
    {
        $computerDiscard = null;
        $lastFloorCard = null;
        $computerCardToPlayIndex = null;

        do {
            $this->checkEndGame();
            $gameData = $this->skitGubbe->getGameData();
            /**
             * @var SkitGubbeHand $floor
             */
            $floor = $gameData["floor"];
            /**
             * @var SkitGubbeHand $computerHand
             */
            $computerHand = $gameData["computerHand"];
            $floorCards = $floor->getCards();
            $lastFloorCard = end($floorCards);

            if (!$lastFloorCard || $lastFloorCard->getName() === "2" || $lastFloorCard->getName() === "10") {
                $lastFloorCard = new Card("3", "hearts");
            }

            $cardsAvailability = $this->skitGubbe->availability("computer");

            if ($cardsAvailability[0]) {
                $this->playComputerCardFromVisible($lastFloorCard);
            }

            /**
             * @var int $computerCardToPlayIndex
             */
            $computerCardToPlayIndex = $computerHand->getNextBigger($lastFloorCard, true);

            /**
             * @var Card $computerCardToPlay
             */
            $computerCardToPlay = $computerHand->getNextBigger($lastFloorCard, false);

            /**
             * @var string $computerCardsToPlayName
             */
            $computerCardsToPlayName = $computerCardToPlay->getName();

            /**
             * @var array<int> $computerCardsToPlay
             */
            $computerCardsToPlay = $computerHand->getAllByName($computerCardsToPlayName, true);

            rsort($computerCardsToPlay);

            if ($computerCardsToPlayName != "A" && $computerCardsToPlayName != "10" && $computerCardsToPlayName != "2") {
                foreach ($computerCardsToPlay as $value) {
                    $computerDiscard = $this->skitGubbe->discard("computerHand", $value);
                }
            } else {
                $computerDiscard = $this->skitGubbe->discard("computerHand", $computerCardToPlayIndex);
            }

        } while ($computerDiscard === "ANOTHER_CARD" || $computerDiscard === "CLEAR_FLOOR");
    }

    private function playComputerCardFromVisible(Card $lastFloorCard): void
    {
        $gameData = $this->skitGubbe->getGameData();

        /**
         * @var SkitGubbeHand $computerVisibleCards
         */
        $computerVisibleCards = $gameData["computerVisibleCards"];
        /**
         * @var SkitGubbeHand $computerHiddenCards
         */
        $computerHiddenCards = $gameData["computerHiddenCards"];
        $fromVisible = true;

        /**
         * @var int $index
         */
        $index = $computerVisibleCards->getNextBigger($lastFloorCard, true);
        $cardsAvailability = $this->skitGubbe->availability("computer");

        if ($cardsAvailability[1]) {
            $fromVisible = false;
            $cardCount = count($computerHiddenCards->getCardNames());
            $index = ($cardCount > 1) ? rand(0, $cardCount) : 0;
        }

        $this->skitGubbe->usePlayerFloor("computerHand", $index, $fromVisible);
    }

    /**
     * show all game results from database
     * @return array<mixed>
     */
    private function showResult(ManagerRegistry $doctrine): array
    {
        $entityManager = $doctrine->getManager();

        return $entityManager->getRepository(dbRes::class)->findAll();

    }

    /**
     * save a result to database
     */
    private function saveResult(ManagerRegistry $doctrine, string $name): bool
    {
        $entityManager = $doctrine->getManager();
        $gameData = $this->skitGubbe->getGameData();
        /**
         * @var bool $isWinner
         */
        $isWinner = $gameData["isWinner"];

        if (!empty($name)) {
            $res = new dbRes();
            $res->setName($name);
            $res->setWin($isWinner);

            $entityManager->persist($res);

            $entityManager->flush();

            return true;
        }

        return false;
    }
}
