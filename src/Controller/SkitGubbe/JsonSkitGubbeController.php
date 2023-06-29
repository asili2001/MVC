<?php

namespace App\Controller\SkitGubbe;

use App\Classes\Cards\Card;
use App\Classes\Cards\CardGraphic;
use App\Classes\Cards\CardHand;
use App\Classes\SkitGubbe as SkitGubbeGame;

use App\Util\Returner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JsonSkitGubbeController extends AbstractController
{
    use Returner;

    use SkitGubbeTrait {
        gameInit as private;
        checkEndGame as private;
        sessionSave as private;
        identicalCardsCheck as private;
        checkIntArray as private;
        playCardFromVisible as private;
        playComputerTurn as private;
        playComputerCardFromVisible as private;
    }

    /**
     * @var SkitGubbeGame\Game $skitGubbe
     */
    private $skitGubbe;

    #[Route('/api/proj/play/new', name: 'skitGubbeResetJson')]
    public function skitGubbeResetJson(Request $request): Response
    {
        $request->getSession()->remove("skitgubbe");
        return $this->redirectToRoute("skitGubbePlayJson");
    }

    #[Route('/api/skitgubbe', name: "skitGubbeJson")]
    public function skitGubbeJson(Request $request): Response
    {
        return $this->redirectToRoute("skitGubbePlayJson");
    }

    #[Route('api/proj/play', name: 'skitGubbePlayJson')]
    public function skitGubbePlayJson(Request $request): Response
    {
        $this->gameInit($request);

        $gameData = $this->skitGubbe->getGameData();

        /**
         * @var CardHand $computerHand
         */
        $computerHand = $gameData["computerHand"];
        /**
         * @var cardHand $computerVisibleCards
         */
        $computerVisibleCards = $gameData["computerVisibleCards"];
        /**
         * @var cardHand $computerHiddenCards
         */
        $computerHiddenCards = $gameData["computerHiddenCards"];
        /**
         * @var cardHand $playerHand
         */
        $playerHand = $gameData["playerHand"];
        /**
         * @var cardHand $playerVisibleCards
         */
        $playerVisibleCards = $gameData["playerVisibleCards"];
        /**
         * @var cardHand $playerHiddenCards
         */
        $playerHiddenCards = $gameData["playerHiddenCards"];
        /**
         * @var cardHand $floor
         */
        $floor = $gameData["floor"];
        /**
         * @var cardHand $basket
         */
        $basket = $gameData["basket"];


        $computerHand = $computerHand->getCardNames();
        $playerHand = $playerHand->getCardNames();
        $floor = $floor->getCardNames();
        $basket = $basket->getCardNames();
        $computerVisibleCards = $computerVisibleCards->getCardNames();
        $computerHiddenCards = $computerHiddenCards->getCardNames();
        $playerVisibleCards = $playerVisibleCards->getCardNames();
        $playerHiddenCards = $playerHiddenCards->getCardNames();

        $result = [
            "computerHand" => $computerHand,
            "computerVisibleCards" => $computerVisibleCards,
            "computerHiddenCards" => $computerHiddenCards,
            "playerHand" => $playerHand,
            "playerVisibleCards" => $playerVisibleCards,
            "playerHiddenCards" => $playerHiddenCards,
            "floor" => $floor,
            "basket" => $basket,
            "message" => $gameData["message"],
            "isWinner" => $gameData["isWinner"]
        ];

        $response = new JsonResponse($result, 200);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }
    #[Route('api/proj/play/discard/{cardIndexs}', name: 'skitGubbePlayDiscardJson')]
    public function skitGubbePlayDiscardJson(Request $request, string $cardIndexs): Response
    {
        $this->gameInit($request);
        $this->skitGubbe->setMessage("");
        $cardIndexs = explode(",", $cardIndexs);
        rsort($cardIndexs);

        if (!$this->checkIntArray($cardIndexs)) {
            $this->skitGubbe->setMessage("Indexes have to be type of INT");
            $this->sessionSave($request);
            return $this->redirectToRoute('skitGubbePlayJson');
        }
        $identicalCards = $this->identicalCardsCheck($cardIndexs);
        $playerDiscard = null;


        if (!$identicalCards) {
            $this->skitGubbe->setMessage("Cards Have to be same");
            $this->sessionSave($request);
            return $this->redirectToRoute('skitGubbePlayJson');
        }

        for ($i=0; $i < count($cardIndexs); $i++) {
            $cardIndex = (int)$cardIndexs[$i];
            $this->gameInit($request);
            $this->checkEndGame();

            $cardsAvailability = $this->skitGubbe->availability("player");

            if ($cardsAvailability[0]) {
                $this->playCardFromVisible($cardIndex);
                $cardIndex = 0; // Set card index to 0 to be used from the hand.
            }

            $tryGetCard = $this->skitGubbe->cardExists("playerHand", $cardIndex);

            if (!$tryGetCard) {
                $this->skitGubbe->setMessage("Card not exists");
                $this->sessionSave($request);
                return $this->redirectToRoute('skitGubbePlayJson');
            }

            $playerDiscard = $this->skitGubbe->discard("playerHand", $cardIndex, false);

            $this->sessionSave($request);
        }

        $this->skitGubbe->fillHand("playerHand");

        if ($playerDiscard != "ANOTHER_CARD" && $playerDiscard != "CLEAR_FLOOR") {
            $this->playComputerTurn();
        }

        $this->sessionSave($request);
        return $this->redirectToRoute('skitGubbePlayJson');

    }

}
