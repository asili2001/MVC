<?php

namespace App\Controller;

use App\Classes\DeckOfCards;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function cardsDeck(): Response
    {
        if (isset($_GET["reset"]) && $_GET["reset"] != "false") {
            session_destroy();
        }
        $deck = new DeckOfCards((isset($_GET["jokers"]) && $_GET["jokers"] != "false"));
        $deck->sortCards();

        $data = [
            "deck" => $deck->getCards(),
            "cardsNr" => count($deck->getCards()),
        ];
        return $this->render('playingCard/deck.html.twig', $data);
    }
    #[Route('/card/deck/shuffle', name: "playingCardDeckShuffled")]
    public function shuffleDeck(): Response
    {
        $deck = new DeckOfCards(true);

        $data = [
            "deck" => $deck->shuffleCards(),
            "cardsNr" => count($deck->getCards()),
        ];
        return $this->render('playingCard/deck.html.twig', $data);
    }

    #[Route('/card/deck/draw', name: "playingCardDeckDrawOne")]
    public function drawCard(): Response
    {
        $deck = new DeckOfCards(true);

        $data = [
            "deck" => $deck->drawCard(),
            "cardsNr" => count($deck->getCards()),
        ];
        return $this->render('playingCard/deck.html.twig', $data);
    }

    #[Route('/card/deck/draw/{nrOfCards}', name: "playingCardDeckDrawMultiple")]
    public function drawCards(int $nrOfCards): Response
    {
        $deck = new DeckOfCards(true);

        $data = [
            "deck" => $deck->drawCard($nrOfCards),
            "cardsNr" => count($deck->getCards()),
        ];
        return $this->render('playingCard/deck.html.twig', $data);
    }

    #[Route('/card/deck/deal/{nrOfPlayers}/{nrOfCards}', name: "playingCardDeckDealCards")]
    public function dealCards(int $nrOfPlayers, int $nrOfCards): Response
    {
        $deck = new DeckOfCards(true);

        $data = [
            "players" => $deck->dealCards($nrOfPlayers, $nrOfCards),
            "cardsNr" => count($deck->getCards()),
        ];
        return $this->render('playingCard/deal-cards.html.twig', $data);
    }
}
