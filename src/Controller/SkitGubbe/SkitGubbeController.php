<?php

namespace App\Controller\SkitGubbe;

use App\Classes\Cards\CardHand;
use App\Classes\SkitGubbe as SkitGubbeGame;
use App\Controller\SkitGubbe\SkitGubbeTrait;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class SkitGubbeController extends AbstractController
{
    use SkitGubbeTrait {
        gameInit as private;
        checkEndGame as private;
        sessionSave as private;
        identicalCardsCheck as private;
        checkIntArray as private;
        playCardFromVisible as private;
        playComputerTurn as private;
        playComputerCardFromVisible as private;
        showResult as private;
        saveResult as private;
        createUser as private;
        loginUser as private;
    }
    /**
     * @var SkitGubbeGame\Game $skitGubbe
     */
    private $skitGubbe;


    #[Route('/proj/play/new', name: 'skitGubbeReset')]
    public function skitGubbeReset(Request $request): Response
    {
        $request->getSession()->remove("skitgubbe");
        return $this->redirectToRoute("skitGubbePlay");
    }


    #[Route('/proj', name: "skitGubbe")]
    public function skitGubbe(Request $request): Response
    {
        $session = $request->getSession();
        $ifLoggedIn = $session->get("auth", null);
        if (!is_null($ifLoggedIn)) {
            return $this->redirectToRoute("skitGubbePlay");
        }

        $data = [
            "message" => ""
        ];
        return $this->render('skitGubbe/auth.html.twig', $data);
        // return $this->redirectToRoute("skitGubbePlay");
    }

    #[Route('/proj/auth', name: "skitGubbeAuth", methods: ["post"])]
    public function skitGubbeAuth(Request $request, ManagerRegistry $doctrine): Response
    {
        $data = [
            "message" => ""
        ];

        $name = is_string($request->get('name')) ? $request->get('name') : "";
        $pass = is_string($request->get('pass')) ? $request->get('pass') : "";
        $submit = $request->get('submit');
        

        $session = $request->getSession();
        $ifLoggedIn = $session->get("auth", null);
        if (!is_null($ifLoggedIn)) {
            return $this->redirectToRoute("skitGubbePlay");
        }

        $res = null;


        if ($submit && $submit === "Login") {
            $res = $this->loginUser($request, $doctrine, $name, $pass);
        } elseif ($submit && $submit === "Signup") {
            $res = $this->createUser($request, $doctrine, $name, $pass);
        } else {
            $data["message"] = "Error, please try again";
            return $this->render('skitGubbe/auth.html.twig', $data);
        }

        if ($res === "SUCCESS" || $res === "ALREADY_LOGGED_IN") {
            return $this->redirectToRoute("skitGubbePlay");
        }

        switch ($res) {
            case 'EMPTY_VALUES':
                $data["message"] = "Please fill in all the inputs";
                break;

            case 'USER_ALREADY_REGISTERED':
                $data["message"] = "User already exists";
                break;

            case 'USER_NOT_REGISTERED':
                $data["message"] = "User does not exists";
                break;

            case 'WRONG_PASS':
                $data["message"] = "Wrong password";
                break;
        }

        return $this->render('skitGubbe/auth.html.twig', $data);

        // var_dump($res);
        // return $this->render('skitGubbe/auth.html.twig');
        // return $this->redirectToRoute("skitGubbePlay");
    }


    #[Route('/proj/about', name: "skitGubbeAbout")]
    public function skitGubbeAbout(Request $request): Response
    {
        return $this->render('skitGubbe/about.html.twig');
    }



    #[Route('/proj/play', name: 'skitGubbePlay')]
    public function skitGubbePlay(Request $request): Response
    {
        $session = $request->getSession();
        $ifLoggedIn = $session->get("auth", null);
        if (is_null($ifLoggedIn)) {
            return $this->redirectToRoute("skitGubbe");
        }

        $this->gameInit($request, false);

        $gameData = $this->skitGubbe->getGameData();
        /**
        * @var CardHand $computerHand
        */
        $computerHand = $gameData["computerHand"];
        /**
        * @var CardHand $playerHand
        */
        $playerHand = $gameData["playerHand"];
        /**
        * @var CardHand $floor
        */
        $floor = $gameData["floor"];
        /**
        * @var CardHand $basket
        */
        $basket = $gameData["basket"];
        /**
        * @var CardHand $deck
        */
        $deck = $gameData["deck"];
        /**
        * @var CardHand $computerVisibleCards
        */
        $computerVisibleCards = $gameData["computerVisibleCards"];
        /**
        * @var CardHand $computerHiddenCards
        */
        $computerHiddenCards = $gameData["computerHiddenCards"];
        /**
        * @var CardHand $playerVisibleCards
        */
        $playerVisibleCards = $gameData["playerVisibleCards"];
        /**
        * @var CardHand $playerHiddenCards
        */
        $playerHiddenCards = $gameData["playerHiddenCards"];

        // hide cards
        $computerHand->hideCards();
        $computerHiddenCards->hideCards();
        $playerHiddenCards->hideCards();
        $deck->hideCards();


        // unhide cards
        $playerHand->unhideCards();
        $floor->unhideCards();

        $result = [
            "computerHand" => $computerHand,
            "computerVisibleCards" => $computerVisibleCards,
            "computerHiddenCards" => $computerHiddenCards,
            "playerHand" => $playerHand,
            "playerVisibleCards" => $playerVisibleCards,
            "playerHiddenCards" => $playerHiddenCards,
            "floor" => $floor,
            "basket" => $basket,
            "deck" => $deck,
            "message" => $gameData["message"],
            "isWinner" => $gameData["isWinner"]
        ];
        return $this->render('skitGubbe/play.html.twig', $result);
    }


    #[Route('/proj/play/discard', name: 'skitGubbePlayDiscard', methods: ["post"])]
    public function skitGubbePlayDiscard(Request $request): Response
    {
        $session = $request->getSession();
        $ifLoggedIn = $session->get("auth", null);
        if (is_null($ifLoggedIn)) {
            return $this->redirectToRoute("skitGubbe");
        }


        $this->gameInit($request, false);
        $this->skitGubbe->setMessage("");
        $cardIndexs = is_string($request->get('cardIndexs')) ? $request->get('cardIndexs') : "";
        $cardIndexs = explode(",", $cardIndexs);

        // we do rsort to sort the indexes from smallest to biggest
        // this will make it easy to remove the cards by index without the need to repositioning the hand cards
        rsort($cardIndexs);
        if (!$this->checkIntArray($cardIndexs)) {
            $this->skitGubbe->setMessage("Indexes have to be type of INT");
            $this->sessionSave($request);
            return $this->redirectToRoute('skitGubbePlay');
        }

        $identicalCards = $this->identicalCardsCheck($cardIndexs);
        $playerDiscard = null;


        if (!$identicalCards) {
            $this->skitGubbe->setMessage("Cards Have to be same");
            $this->sessionSave($request);
            return $this->redirectToRoute('skitGubbePlay');
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
                return $this->redirectToRoute('skitGubbePlay');
            }

            $playerDiscard = $this->skitGubbe->discard("playerHand", $cardIndex, false);

            $this->sessionSave($request);

        }

        $this->skitGubbe->fillHand("playerHand");

        if ($playerDiscard != "ANOTHER_CARD" && $playerDiscard != "CLEAR_FLOOR") {
            $this->playComputerTurn();
        }

        $this->sessionSave($request);
        return $this->redirectToRoute('skitGubbePlay');

    }

    #[Route('/proj/play/save', name: 'skitGubbePlaySave', methods: ["post"])]
    public function skitGubbePlaySave(Request $request, ManagerRegistry $doctrine): Response
    {
        $session = $request->getSession();
        $ifLoggedIn = $session->get("auth", null);
        if (is_null($ifLoggedIn)) {
            return $this->redirectToRoute("skitGubbe");
        }

        $this->gameInit($request);

        $this->saveResult($request, $doctrine);

        return $this->redirectToRoute('skitGubbeReset');
    }

    #[Route('/proj/results', name: 'skitGubbeResults')]
    public function skitGubbeResults(Request $request, ManagerRegistry $doctrine): Response
    {
        $session = $request->getSession();
        $ifLoggedIn = $session->get("auth", null);
        if (is_null($ifLoggedIn)) {
            return $this->redirectToRoute("skitGubbe");
        }

        $result = ["data" => $this->showResult($doctrine)];

        return $this->render('skitGubbe/results.html.twig', $result);
    }
}
