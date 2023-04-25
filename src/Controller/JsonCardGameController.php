<?php

namespace App\Controller;

use App\Classes\BlackJackHand;
use App\Classes\CardGameFuncs;
use App\Traits\Returner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JsonCardGameController extends AbstractController
{
    use Returner;
    #[Route('/api/game', name: "cardGameJson")]
    public function cardGameHome(): Response
    {
        return $this->redirectToRoute("cardGamePlayJson");
    }

    #[Route('api/game/play', name: 'cardGamePlayJson')]
    public function cardGamePlay(Request $request): Response
    {
        $cardGameFuncs = new CardGameFuncs();
        $data = $cardGameFuncs->startGame($request);
        /**
         * @var BlackJackHand $dealerHand
         */
        $dealerHand = $data['dealerHand'];

        /**
         * @var BlackJackHand $playerHand
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
        // print_r($data);

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/game/play/new', name: 'cardGameResetJson')]
    public function cardGameReset(Request $request): Response
    {
        $cardGameFuncs = new CardGameFuncs();
        $cardGameFuncs->reset($request);
        return $this->redirectToRoute("cardGamePlayJson");
    }

    #[Route('/api/game/play/hit', name: 'cardGameHitJson')]
    public function cardGameHit(Request $request): Response
    {
        $cardGameFuncs = new CardGameFuncs();
        $cardGameFuncs->hit($request);
        return $this->redirectToRoute("cardGamePlayJson");
    }
    #[Route('/api/game/play/stand', name: 'cardGameStandJson')]
    public function cardGameStand(Request $request): Response
    {
        $cardGameFuncs = new CardGameFuncs();
        $cardGameFuncs->stand($request);
        return $this->redirectToRoute("cardGamePlayJson");
    }
}
