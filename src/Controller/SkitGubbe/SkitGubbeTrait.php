<?php

namespace App\Controller\SkitGubbe;

use App\Classes\Cards\Card;
use App\Classes\Cards\CardHand;
use App\Classes\Cards\DeckOfCards;
use App\Classes\SkitGubbe\SkitGubbeHand;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Classes\SkitGubbe as SkitGubbeGame;
use App\Entity\Skitgubbe as ResultTable;
use App\Entity\Users as UsersTable;

trait SkitGubbeTrait
{
    protected bool $isApi = false;

    private function gameInit(Request $request, bool $miniGame, bool $useApi): void
    {
        $session = $request->getSession();
        /**
         * @var array<mixed> $gameSession
         */
        $gameSession = $session->get("skitgubbe", []);

        $this->isApi = $useApi;

        $cardsToDelete = $miniGame ? 30 : 0;

        $this->skitGubbe = new SkitGubbeGame\Game($gameSession, $cardsToDelete);
        $this->skitGubbe->checkWinner();
        $session->set("skitgubbe", $this->skitGubbe->getGameData());
    }

    private function checkEndGame(): void
    {
        $this->skitGubbe->checkWinner();

        $redirect = "skitGubbePlayJson";

        if (!$this->isApi) {
            $redirect = "skitGubbePlay";
        }

        if (!is_null($this->skitGubbe->getGameData()["isWinner"])) {
            exit($this->redirectToRoute($redirect));
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
            $hand = $cardsAvailability[1] ? "playerHiddenCards" : "playerVisibleCards";
        }

        $gameData = $this->skitGubbe->getGameData();
        /**
         * @var SkitGubbeHand $handObj
         */
        $handObj = $gameData[$hand];
        $handCards = $handObj->getCards();

        if (count($cardIndexs) <= 1 || count((array) $gameData["deck"]) < count($cardIndexs)) {
            return true; // Not enough cards to compare or only one card selected
        }

        $cardName = $handCards[$cardIndexs[0]]->getName() ?? "";

        foreach ($cardIndexs as $cardIndex) {
            if (!isset($handCards[$cardIndex]) || $handCards[$cardIndex]->getName() !== $cardName) {
                return false; // Cards are not identical
            }
        }

        return true; // All selected cards are identical
    }

    /**
     * checks if all items in array is type of int
     * @param array<mixed> $array
     */
    private function checkIntArray(array $array): bool
    {
        $allNumeric = true;
        foreach ($array as $key) {
            if (!(is_numeric($key))) {
                $allNumeric = false;
                break;
            }
        }

        return $allNumeric;
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
        $compCardToPlayIndex = null;

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
             * @var int $compCardToPlayIndex
             */
            $compCardToPlayIndex = $computerHand->getNextBiggerIndex($lastFloorCard);

            /**
             * @var Card $computerCardToPlay
             */
            $computerCardToPlay = $computerHand->getNextBigger($lastFloorCard);

            /**
             * @var string $compCardsToPlayName
             */
            $compCardsToPlayName = $computerCardToPlay->getName();

            /**
             * @var array<int> $computerCardsToPlay
             */
            $computerCardsToPlay = $computerHand->getAllIndexByName($compCardsToPlayName);

            rsort($computerCardsToPlay);

            if ($compCardsToPlayName != "A" && $compCardsToPlayName != "10" && $compCardsToPlayName != "2") {
                foreach ($computerCardsToPlay as $value) {
                    $computerDiscard = $this->skitGubbe->discard("computerHand", $value, true);
                }
            } else {
                $computerDiscard = $this->skitGubbe->discard("computerHand", $compCardToPlayIndex, true);
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
        $index = $computerVisibleCards->getNextBiggerIndex($lastFloorCard);
        $cardsAvailability = $this->skitGubbe->availability("computer");

        if ($cardsAvailability[1]) {
            $fromVisible = false;
            $cardCount = count($computerHiddenCards->getCardNames());
            $index = ($cardCount > 1) ? rand(0, $cardCount - 1) : 0;
        }

        $this->skitGubbe->usePlayerFloor("computerHand", $index, $fromVisible);
    }

    private function createUser(Request $request, ManagerRegistry $doctrine, string $name, string $pass): string
    {
        $session = $request->getSession();
        $ifLoggedIn = $session->get("auth", null);

        if (!is_null($ifLoggedIn)) {
            return "ALREADY_LOGGED_IN";
        }

        if (empty($name) || empty($pass)) {
            return "EMPTY_VALUES";
        }

        $entityManager = $doctrine->getManager();
        $userExists = $entityManager->getRepository(UsersTable::class)->findOneBy(["name" => $name]);

        $res = new UsersTable();

        if ($userExists) {
            return "USER_ALREADY_REGISTERED";
        }

        $res->setName($name);
        $res->setPass($pass);

        $entityManager->persist($res);

        $entityManager->flush();

        $session->set("auth", ["name" => $name]);
        return "SUCCESS";
    }

    private function loginUser(Request $request, ManagerRegistry $doctrine, string $name, string $pass): string
    {
        $session = $request->getSession();
        $ifLoggedIn = $session->get("auth", null);

        if (!is_null($ifLoggedIn)) {
            return "ALREADY_LOGGED_IN";
        }

        if (empty($name) || empty($pass)) {
            return "EMPTY_VALUES";
        }

        $entityManager = $doctrine->getManager();
        $userExists = $entityManager->getRepository(UsersTable::class)->findOneBy(["name" => $name]);

        if (!$userExists) {
            return "USER_NOT_REGISTERED";
        }

        if ($userExists->getPass() !== $pass) {
            return "WRONG_PASS";
        }

        $session->set("auth", ["name" => $name]);
        return "SUCCESS";
    }

    /**
     * show all game results from database
     * @return array<mixed>
     */
    private function showResult(ManagerRegistry $doctrine): array
    {
        $entityManager = $doctrine->getManager();

        return $entityManager->getRepository(ResultTable::class)->findAll();

    }

    /**
     * save a result to database
     */
    private function saveResult(Request $request, ManagerRegistry $doctrine): bool
    {
        $session = $request->getSession();
        $auth = $session->get("auth", null);
        $name = (is_array($auth) && isset($auth["name"])) ? $auth["name"] : "";
        $entityManager = $doctrine->getManager();
        $gameData = $this->skitGubbe->getGameData();
        /**
         * @var bool $isWinner
         */
        $isWinner = $gameData["isWinner"];

        var_dump($name);

        if (!empty($name)) {
            $res = new ResultTable();
            $res->setName($name);
            $res->setWin($isWinner);

            $entityManager->persist($res);

            $entityManager->flush();

            return true;
        }

        return false;
    }
}
