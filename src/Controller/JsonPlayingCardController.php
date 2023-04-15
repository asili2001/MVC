<?php

namespace App\Controller;

use App\Classes\DeckOfCards;
use App\CustomExceptions\EmptyDeckException;
use App\Traits\Returner;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JsonPlayingCardController extends AbstractController
{
    use Returner;
    #[Route('/api', name: "playingCardHomeJson")]
    public function home(): Response
    {
        return $this->render('playingCard/home.html.twig');
    }
    #[Route('/api/deck', name: 'jsonDeck')]
    public function cardDeckJson(): Response
    {
        $deck = new DeckOfCards((isset($_GET["jokers"]) && $_GET["jokers"] != "false"));
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
        $res = $this->JsonReturner($cardData, $statusCode, "Success");

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/shuffle', name: "playingCardDeckShuffledJson")]
    public function shuffleDeckJson(): Response
    {
        $deck = new DeckOfCards(true);

        $cards = $deck->shuffleCards();

        $cardData = array();
        foreach ($cards as $card) {
            $cardData[] = [
                "symbol" => $card->getSymbol(),
                "name" => $card->getName()
            ];
        }

        $statusCode = 200;
        $res = $this->JsonReturner($cardData, $statusCode, "Success");
        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/draw', name: "playingCardDeckDrawOneJson")]
    public function drawCardJson(): Response
    {
        $deck = new DeckOfCards(true);
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
        $res = $this->JsonReturner($cardData, $statusCode, $errorMessage);

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/draw/{nrOfCards}', name: "playingCardDeckDrawMultipleJson")]
    public function drawCardsJson(int $nrOfCards): Response
    {
        $deck = new DeckOfCards(true);
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
        $res = $this->JsonReturner($cardData, $statusCode, $errorMessage);

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/deck/deal/{nrOfPlayers}/{nrOfCards}', name: "playingCardDeckDealCardsJson")]
    public function dealCardsJson(int $nrOfPlayers, int $nrOfCards): Response
    {
        $deck = new DeckOfCards(true);
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
        $res = $this->JsonReturner($playerData, $statusCode, $errorMessage);

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;

        // $data = [
        //     "players" => $deck->dealCards($nrOfPlayers, $nrOfCards),
        //     "cardsNr" => count($deck->getCards()),
        // ];
        // return $this->render('playingCard/deal-cards.html.twig', $data);
    }
}
