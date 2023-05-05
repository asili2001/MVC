<?php

namespace App\Controller\BlackJackGame;

use App\Classes\BlackJackGame as BlackJackGame;

use App\Util\Returner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JsonCardGameController extends AbstractController
{
    use Returner;

    /**
     * @var BlackJackGame\Game $blackJackGame
     */
    private $blackJackGame;
    private function gameInit(Request $request): void
    {
        $session = $request->getSession();
        /**
         * @var array<mixed> $gameSession
         */
        $gameSession = $session->get("blackjack", []);

        $this->blackJackGame = new BlackJackGame\Game($gameSession);
        $session->set("blackjack", $this->blackJackGame->getGameData());
    }

    #[Route('/api/game', name: "cardGameJson")]
    public function cardGameJson(): Response
    {
        return $this->redirectToRoute("cardGamePlayJson");
    }

    #[Route('api/game/play', name: 'cardGamePlayJson')]
    public function cardGamePlayJson(Request $request): Response
    {
        $session = $request->getSession();
        $this->gameInit($request);
        $session->set("blackjack", $this->blackJackGame->getGameData());
        $data = $this->blackJackGame->getGameData();
        /**
         * @var BlackJackGame\BlackJackHand $dealerHand
         */
        $dealerHand = $data['dealerHand'];

        /**
         * @var BlackJackGame\BlackJackHand $playerHand
         */
        $playerHand = $data['playerHand'];
        /**
         * @var null|string $winner
         */
        $winner = $data['winner'];

        $dealerCards = [];
        $playerCards = [];
        $dealerCardsCount = count($dealerHand->getCards());
        $playerCardsCount = count($playerHand->getCards());
        for ($i=0; $i < $dealerCardsCount; $i++) {
            if (array_values($dealerHand->getCards())[$i]->isHidden()) {
                $dealerCards[] = "Hidden";
            } elseif (!array_values($dealerHand->getCards())[$i]->isHidden()) {
                $dealerCards[] = array_values($dealerHand->getCards())[$i]->getName();
            }
        }
        for ($i=0; $i < $playerCardsCount; $i++) {
            $playerCards[] = array_values($playerHand->getCards())[$i]->getName();
        }
        $finalData = [
            "dealerCards" => $dealerCards,
            "playerCards" => $playerCards,
            "winner" => $winner ?? "Unknown"
        ];
        $statusCode = 200;
        $res = $this->arrReturner(false, $finalData, $statusCode, "Success");

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route('/api/game/play/new', name: 'cardGameResetJson')]
    public function cardGameResetJson(Request $request): Response
    {
        $request->getSession()->remove("blackjack");
        return $this->redirectToRoute("cardGamePlayJson");
    }

    #[Route('/api/game/play/hit', name: 'cardGameHitJson')]
    public function cardGameHitJson(Request $request): Response
    {
        $this->gameInit($request);
        $this->blackJackGame->hit();
        $session = $request->getSession();
        $session->set("blackjack", $this->blackJackGame->getGameData());
        print_r($this->blackJackGame->getGameData());
        return $this->redirectToRoute("cardGamePlayJson");
    }
    #[Route('/api/game/play/stand', name: 'cardGameStandJson')]
    public function cardGameStandJson(Request $request): Response
    {
        $this->gameInit($request);
        $this->blackJackGame->stand();
        $session = $request->getSession();
        $session->set("blackjack", $this->blackJackGame->getGameData());
        return $this->redirectToRoute("cardGamePlayJson");
    }
}
