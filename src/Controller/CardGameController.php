<?php

namespace App\Controller;

use App\Classes\CardGameFuncs;
use App\Classes\BlackJackHand;
use App\Classes\DeckOfCards;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardGameController extends AbstractController
{
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
        $cardGameFuncs = new CardGameFuncs();
        $data = $cardGameFuncs->startGame($request);
        return $this->render('game/play.html.twig', $data);
    }

    #[Route('/game/play/new', name: 'cardGameReset')]
    public function cardGameReset(Request $request): Response
    {
        $cardGameFuncs = new CardGameFuncs();
        $cardGameFuncs->reset($request);
        return $this->redirectToRoute("cardGamePlay");
    }

    #[Route('/game/play/hit', name: 'cardGameHit')]
    public function cardGameHit(Request $request): Response
    {
        $cardGameFuncs = new CardGameFuncs();
        $cardGameFuncs->hit($request);
        return $this->redirectToRoute("cardGamePlay");
    }
    #[Route('/game/play/stand', name: 'cardGameStand')]
    public function cardGameStand(Request $request): Response
    {
        $cardGameFuncs = new CardGameFuncs();
        $cardGameFuncs->stand($request);
        return $this->redirectToRoute("cardGamePlay");
    }
}
