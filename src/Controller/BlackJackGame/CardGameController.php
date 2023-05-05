<?php

namespace App\Controller\BlackJackGame;

use App\Classes\BlackJackGame as BlackJackGame;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardGameController extends AbstractController
{
    /**
     * @var BlackJackGame\Game $blackJackGame
     */
    private $blackJackGame;

    /**
     * Initialaize game
     */
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

    #[Route('/game', name: 'cardGame')]
    public function cardGameHome(): Response
    {
        return $this->render('game/home.html.twig');
    }
    #[Route('/game/doc', name: 'cardGameDoc')]
    public function cardGameDoc(): Response
    {
        return $this->render('game/doc.html.twig');
    }

    #[Route('/game/play', name: 'cardGamePlay')]
    public function cardGameStart(Request $request): Response
    {
        $this->gameInit($request);
        $data = $this->blackJackGame->getGameData();
        return $this->render('game/play.html.twig', $data);
    }

    #[Route('/game/play/new', name: 'cardGameReset')]
    public function cardGameReset(Request $request): Response
    {
        $request->getSession()->remove("blackjack");
        return $this->redirectToRoute("cardGamePlay");
    }

    #[Route('/game/play/hit', name: 'cardGameHit')]
    public function cardGameHit(Request $request): Response
    {
        $this->gameInit($request);
        $this->blackJackGame->hit();
        $session = $request->getSession();
        $session->set("blackjack", $this->blackJackGame->getGameData());
        return $this->redirectToRoute("cardGamePlay");
    }
    #[Route('/game/play/stand', name: 'cardGameStand')]
    public function cardGameStand(Request $request): Response
    {
        $this->gameInit($request);
        $this->blackJackGame->stand();
        $session = $request->getSession();
        $session->set("blackjack", $this->blackJackGame->getGameData());
        return $this->redirectToRoute("cardGamePlay");
    }
}
