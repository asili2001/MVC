<?php

namespace App\Controller;

use App\Classes\DeckOfCards;
use App\CustomExceptions\EmptyDeckException;
use App\Traits\Returner;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JsonPlayingCardController extends AbstractController
{
    use Returner;
    #[Route('/api', name: "playingCardHomeJson")]
    public function home(): Response
    {
        return $this->render('api.html.twig');
    }
    #[Route('/api/deck', name: 'jsonDeck')]
    public function cardDeckJson(Request $request): Response
    {
        $session = $request->getSession();
        $jokersQuery = $request->query->get("jokers");
        $deck = new DeckOfCards($session, "playingCards");
        if (isset($jokersQuery) && $jokersQuery != "false") {
            $deck->hasJokers();
        }
        $deck->sortCards();

        $cards = $deck->getCards();

        $cardData = array();
        foreach ($cards as $card) {
            $cardData[] = [
                "symbol" => $card->getSymbol(),
                "name" => $card->getName()
            ];
        }
        $statusCode = 200;
        $res = $this->arrReturner(false, $cardData, $statusCode, "Success");

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/shuffle', name: "playingCardDeckShuffledJson")]
    public function shuffleDeckJson(Request $request): Response
    {
        $session = $request->getSession();
        $deck = new DeckOfCards($session, "playingCards");

        // shuffle
        $deck->shuffleCards();
        $cards = $deck->getCards();

        $cardData = array();
        foreach ($cards as $card) {
            $cardData[] = [
                "symbol" => $card->getSymbol(),
                "name" => $card->getName()
            ];
        }

        $statusCode = 200;
        $res = $this->arrReturner(false, $cardData, $statusCode, "Success");
        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/draw', name: "playingCardDeckDrawOneJson")]
    public function drawCardJson(Request $request): Response
    {
        $session = $request->getSession();
        $deck = new DeckOfCards($session, "playingCards");
        $cardData = array();
        $errorMessage = "";
        $statusCode = 200;

        try {
            $cards = $deck->drawCard();
            foreach ($cards as $card) {
                $cardData[] = [
                    "symbol" => $card->getSymbol(),
                    "name" => $card->getName()
                ];
            }
        } catch (EmptyDeckException $e) {
            $errorMessage = $e->getMessage();
            $statusCode = 404;
        }
        $res = $this->arrReturner(false, $cardData, $statusCode, $errorMessage);

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/draw/{nrOfCards}', name: "playingCardDeckDrawMultipleJson")]
    public function drawCardsJson(Request $request, int $nrOfCards): Response
    {
        $session = $request->getSession();
        $deck = new DeckOfCards($session, "playingCards");
        $cardData = array();
        $errorMessage = "";
        $statusCode = 200;

        try {
            $cards = $deck->drawCard($nrOfCards);
            foreach ($cards as $card) {
                $cardData[] = [
                    "symbol" => $card->getSymbol(),
                    "name" => $card->getName()
                ];
            }
        } catch (EmptyDeckException $e) {
            $errorMessage = $e->getMessage();
            $statusCode = 404;
        }
        $res = $this->arrReturner(false, $cardData, $statusCode, $errorMessage);

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/deal/{nrOfPlayers}/{nrOfCards}', name: "playingCardDeckDealCardsJson")]
    public function dealCardsJson(Request $request, int $nrOfPlayers, int $nrOfCards): Response
    {
        $session = $request->getSession();
        $deck = new DeckOfCards($session, "playingCards");
        $playerData = array();
        $errorMessage = "";
        $statusCode = 200;

        try {
            $players = $deck->dealCards($nrOfPlayers, $nrOfCards);
            $cards = array();
            foreach ($players as $key => $hand) {
                $cards = $hand->getCards();

                foreach ($cards as $card) {
                    $playerData[$key][] = [
                        "symbol" => $card->getSymbol(),
                        "name" => $card->getName()
                    ];
                }

            }

        } catch (EmptyDeckException $e) {
            $errorMessage = $e->getMessage();
            $statusCode = 404;
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $statusCode = 500;
        }
        $res = $this->arrReturner(false, $playerData, $statusCode, $errorMessage);

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}
