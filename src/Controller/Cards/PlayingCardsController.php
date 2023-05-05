<?php

namespace App\Controller\Cards;

use App\Classes\Cards\Card;
use App\Classes\Cards\DeckOfCards;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayingCardsController extends AbstractController
{
    #[Route('/card', name: "playingCardHome")]
    public function home(): Response
    {
        return $this->render('playingCard/home.html.twig');
    }

    #[Route('/card/deck', name: "playingCardDeck")]
    public function cardsDeck(Request $request): Response
    {
        $session = $request->getSession();
        $resetQuery = $request->query->get("reset");
        $jokersQuery = $request->query->get("jokers");
        if (isset($resetQuery) && $resetQuery != "false") {
            $session->remove("playingCards");
        }
        /**
         * @var array<Card> $playingCardsSession
         */
        $playingCardsSession = $session->get("playingCards");
        $deck = new DeckOfCards($playingCardsSession);
        if (isset($jokersQuery) && $jokersQuery != "false") {
            $deck->hasJokers();
        }
        $deck->sortCards();

        $data = [
            "deck" => $deck->getCards(),
            "cardsNr" => count($deck->getCards()),
        ];
        return $this->render('playingCard/deck.html.twig', $data);
    }
    #[Route('/card/deck/shuffle', name: "playingCardDeckShuffled")]
    public function shuffleDeck(Request $request): Response
    {
        $session = $request->getSession();
        /**
         * @var array<Card> $playingCardsSession
         */
        $playingCardsSession = $session->get("playingCards");
        $deck = new DeckOfCards($playingCardsSession);
        $deck->shuffleCards();
        $data = [
            "deck" => $deck->getCards(),
            "cardsNr" => count($deck->getCards()),
        ];
        return $this->render('playingCard/deck.html.twig', $data);
    }

    #[Route('/card/deck/draw', name: "playingCardDeckDrawOne")]
    public function drawCard(Request $request): Response
    {
        $session = $request->getSession();
        /**
         * @var array<Card> $playingCardsSession
         */
        $playingCardsSession = $session->get("playingCards");
        $deck = new DeckOfCards($playingCardsSession);

        $data = [
            "deck" => $deck->drawCard(),
            "cardsNr" => count($deck->getCards()),
        ];
        return $this->render('playingCard/deck.html.twig', $data);
    }

    #[Route('/card/deck/draw/{nrOfCards}', name: "playingCardDeckDrawMultiple")]
    public function drawCards(Request $request, int $nrOfCards): Response
    {
        $session = $request->getSession();
        /**
         * @var array<Card> $playingCardsSession
         */
        $playingCardsSession = $session->get("playingCards");
        $deck = new DeckOfCards($playingCardsSession);

        $data = [
            "deck" => $deck->drawCard($nrOfCards),
            "cardsNr" => count($deck->getCards()),
        ];
        return $this->render('playingCard/deck.html.twig', $data);
    }

    #[Route('/card/deck/deal/{nrOfPlayers}/{nrOfCards}', name: "playingCardDeckDealCards")]
    public function dealCards(Request $request, int $nrOfPlayers, int $nrOfCards): Response
    {
        $session = $request->getSession();
        /**
         * @var array<Card> $playingCardsSession
         */
        $playingCardsSession = $session->get("playingCards");
        $deck = new DeckOfCards($playingCardsSession);

        $data = [
            "players" => $deck->dealCards($nrOfPlayers, $nrOfCards),
            "cardsNr" => count($deck->getCards()),
        ];
        return $this->render('playingCard/deal-cards.html.twig', $data);
    }
}
