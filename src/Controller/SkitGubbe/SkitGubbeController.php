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
        $session = $request->getSession();
        $session->remove("skitgubbe");
        $session->remove("minigame");
        return $this->redirectToRoute("skitGubbePlay");
    }

    #[Route('/proj/minigame', name: 'skitGubbeMiniGame')]
    public function skitGubbeMini(Request $request): Response
    {
        $session = $request->getSession();
        $session->set("minigame", true);
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
    }


    #[Route('/proj/auth', name: "skitGubbeAuth", methods: ["post"])]
    public function skitGubbeAuth(Request $request, ManagerRegistry $doctrine): Response
    {
        /**
         * @var string $name
         */
        $name = $request->get('name', '');
        /**
         * @var string $pass
         */
        $pass = $request->get('pass', '');
        $submit = $request->get('submit');

        $session = $request->getSession();
        $isLoggedIn = $session->get("auth", null);

        if (!is_null($isLoggedIn)) {
            return $this->redirectToRoute("skitGubbePlay");
        }

        if ($submit === "Login") {
            $res = $this->loginUser($request, $doctrine, $name, $pass);
        } elseif ($submit === "Signup") {
            $res = $this->createUser($request, $doctrine, $name, $pass);
        } elseif ($submit !== "Signup" || $submit !== "Login") {
            return $this->render('skitGubbe/auth.html.twig', [
                "message" => "Error, please try again"
            ]);
        }

        if ($res === "SUCCESS" || $res === "ALREADY_LOGGED_IN") {
            return $this->redirectToRoute("skitGubbePlay");
        }

        $data = $this->getResponseData($res);
        return $this->render('skitGubbe/auth.html.twig', $data);
    }

    /**
     * @return array<mixed>
     */
    private function getResponseData(string $res): array
    {
        $messages = [
            'EMPTY_VALUES' => "Please fill in all the inputs",
            'USER_ALREADY_REGISTERED' => "User already exists",
            'USER_NOT_REGISTERED' => "User does not exist",
            'WRONG_PASS' => "Wrong password"
        ];

        $message = $messages[$res] ?? "";

        return [
            "message" => $message
        ];
    }



    #[Route('/proj/about', name: "skitGubbeAbout")]
    public function skitGubbeAbout(): Response
    {
        return $this->render('skitGubbe/about.html.twig');
    }
    #[Route('/proj/about/database', name: "skitGubbeAboutDb")]
    public function skitGubbeAboutDb(): Response
    {
        return $this->render('skitGubbe/about_db.html.twig');
    }



    #[Route('/proj/play', name: 'skitGubbePlay')]
    public function skitGubbePlay(Request $request): Response
    {
        $session = $request->getSession();
        $ifLoggedIn = $session->get("auth", null);
        if (is_null($ifLoggedIn)) {
            return $this->redirectToRoute("skitGubbe");
        }

        /**
         * @var boolean $miniGameSess
         */
        $miniGameSess = $session->get("minigame", false);

        $this->gameInit($request, $miniGameSess, false);

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


    #[Route('/proj/play/discard', name: 'skitGubbePlayDiscard', methods: ["POST"])]
    public function skitGubbePlayDiscard(Request $request): Response
    {
        $session = $request->getSession();
        $isLoggedIn = $session->get("auth", null);

        if (is_null($isLoggedIn)) {
            return $this->redirectToRoute("skitGubbe");
        }

        /**
         * @var boolean $miniGameSess
         */
        $miniGameSess = $session->get("minigame", false);

        $this->gameInit($request, $miniGameSess, false);
        $this->skitGubbe->setMessage("");
        /**
         * @var string $cardIndexs
         */
        $cardIndexs = $request->get('cardIndexs', '');

        $cardIndexsArr = explode(",", $cardIndexs);

        if (!$this->checkIntArray($cardIndexsArr)) {
            $this->skitGubbe->setMessage("Indexes have to be of type INT");
            $this->sessionSave($request);
            return $this->redirectToRoute('skitGubbePlay');
        }

        $identicalCards = $this->identicalCardsCheck($cardIndexsArr);

        if (!$identicalCards) {
            $this->skitGubbe->setMessage("Cards have to be the same");
            $this->sessionSave($request);
            return $this->redirectToRoute('skitGubbePlay');
        }

        foreach ($cardIndexsArr as $cardIndex) {
            $cardIndex = (int) $cardIndex;
            /**
             * @var boolean $miniGameSess
             */
            $miniGameSess = $session->get("minigame", false);

            $this->gameInit($request, $miniGameSess, false);
            $this->checkEndGame();

            $cardsAvailability = $this->skitGubbe->availability("player");

            if ($cardsAvailability[0]) {
                $this->playCardFromVisible($cardIndex);
                $cardIndex = 0;
            }

            if (!$this->skitGubbe->cardExists("playerHand", $cardIndex)) {
                $this->skitGubbe->setMessage("Card does not exist");
                $this->sessionSave($request);
                return $this->redirectToRoute('skitGubbePlay');
            }

            $playerDiscard = $this->skitGubbe->discard("playerHand", $cardIndex, false);
            $this->sessionSave($request);
        }

        $this->skitGubbe->fillHand("playerHand");

        if ($playerDiscard !== "ANOTHER_CARD" && $playerDiscard !== "CLEAR_FLOOR") {
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

        /**
         * @var boolean $miniGameSess
         */
        $miniGameSess = $session->get("minigame", false);

        $this->gameInit($request, $miniGameSess, false);

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
