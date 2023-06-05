<?php

namespace App\Controller\Cards;

use App\Classes\Cards\Card;
use App\Classes\Cards\DeckOfCards;
use App\CustomExceptions\EmptyDeckException;
use App\Util\Returner;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JsonPlayingCardController extends AbstractController
{
    use Returner;


    private function buildCardData(array $cards): array
    {
        $cardData = [];
        foreach ($cards as $card) {
            $cardData[] = [
                "symbol" => $card->getSymbol(),
                "name" => $card->getName()
            ];
        }
        return $cardData;
    }


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
        /**
         * @var array<Card> $playingCardsSession
         */
        $playingCardsSession = $session->get("playingCards") ?? array();
        $deck = new DeckOfCards($playingCardsSession);
        if (isset($jokersQuery) && $jokersQuery != "false") {
            $deck->hasJokers();
        }
        $deck->sortCards();

        $cards = $deck->getCards();

        $cardData = $this->buildCardData($cards);

        $statusCode = 200;
        $res = $this->arrReturner(false, $cardData, $statusCode, "Success");

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        $session->set("playingCards", $deck->getCards());
        return $response;
    }

    #[Route('/api/deck/shuffle', name: "playingCardDeckShuffledJson")]
    public function shuffleDeckJson(Request $request): Response
    {
        $session = $request->getSession();
        /**
         * @var array<Card> $playingCardsSession
         */
        $playingCardsSession = $session->get("playingCards") ?? array();
        $deck = new DeckOfCards($playingCardsSession);

        // shuffle
        $deck->shuffleCards();
        $cards = $deck->getCards();

        $cardData = $this->buildCardData($cards);

        $statusCode = 200;
        $res = $this->arrReturner(false, $cardData, $statusCode, "Success");
        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        $session->set("playingCards", $deck->getCards());
        return $response;
    }

    #[Route('/api/deck/draw', name: "playingCardDeckDrawOneJson")]
    public function drawCardJson(Request $request): Response
    {
        $session = $request->getSession();
        /**
         * @var array<Card> $playingCardsSession
         */
        $playingCardsSession = $session->get("playingCards") ?? array();
        $deck = new DeckOfCards($playingCardsSession);
        $errorMessage = "";
        $statusCode = 200;

        try {
            $cards = $deck->drawCard();
            $cardData = $this->buildCardData($cards);
        } catch (EmptyDeckException $e) {
            $errorMessage = $e->getMessage();
            $statusCode = 404;
        }
        $res = $this->arrReturner(false, $cardData, $statusCode, $errorMessage);

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        $session->set("playingCards", $deck->getCards());
        return $response;
    }

    #[Route('/api/deck/draw/{nrOfCards}', name: "playingCardDeckDrawMultipleJson")]
    public function drawCardsJson(Request $request, int $nrOfCards): Response
    {
        $session = $request->getSession();
        /**
         * @var array<Card> $playingCardsSession
         */
        $playingCardsSession = $session->get("playingCards") ?? array();
        $deck = new DeckOfCards($playingCardsSession);
        $errorMessage = "";
        $statusCode = 200;

        try {
            $cards = $deck->drawCard($nrOfCards);
            $cardData = $this->buildCardData($cards);
        } catch (EmptyDeckException $e) {
            $errorMessage = $e->getMessage();
            $statusCode = 404;
        }
        $res = $this->arrReturner(false, $cardData, $statusCode, $errorMessage);

        $response = new JsonResponse($res, $statusCode);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        $session->set("playingCards", $deck->getCards());
        return $response;
    }

    #[Route('/api/deck/deal/{nrOfPlayers}/{nrOfCards}', name: "playingCardDeckDealCardsJson")]
    public function dealCardsJson(Request $request, int $nrOfPlayers, int $nrOfCards): Response
    {
        $session = $request->getSession();
        /**
         * @var array<Card> $playingCardsSession
         */
        $playingCardsSession = $session->get("playingCards") ?? array();
        $deck = new DeckOfCards($playingCardsSession);
        $playerData = array();
        $errorMessage = "";
        $statusCode = 200;

        try {
            $players = $deck->dealCards($nrOfPlayers, $nrOfCards);
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
        $session->set("playingCards", $deck->getCards());
        return $response;
    }
}
