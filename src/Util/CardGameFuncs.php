<?php

namespace App\Util;

use App\Classes\BlackJackHand;
use App\Classes\DeckOfCards;
use Symfony\Component\HttpFoundation\Request;

trait CardGameFuncs
{
    /**
     * Check if game is already started by checking session data.
     * If not, a new game will be created.
     * @property Request $request
     * @return array<BlackJackHand|string|null>
     */
    public static function startGame(Request $request): array
    {
        $session = $request->getSession();
        /**
         * @var array<BlackJackHand> $gameSession
         */
        $gameSession = $session->get("blackjack");
        if (isset($gameSession["dealerHand"]) &&
        isset($gameSession["playerHand"])) {
            return $gameSession;
        }
        $deck = new DeckOfCards($session, "blackjackCards");
        $deck->resetCards();
        $deck->shuffleCards();
        $deal = array_values($deck->dealCards(2, 4));
        // hide first dealer card
        array_values($deal[0]->getCards(0))[0]->hide();

        $dealerHand = new BlackJackHand($deal[0]->getCards());
        $playerHand = new BlackJackHand($deal[1]->getCards());

        $data = [
            "dealerHand" => $dealerHand,
            "playerHand" => $playerHand,
            "winner" => null
        ];
        $session->set('blackjack', $data);
        return $data;
    }
    public static function reset(Request $request): bool
    {
        $request->getSession()->remove("blackjack");
        return true;
    }

    public static function hit(Request $request): bool
    {
        $session = $request->getSession();
        /**
         * @var array<BlackJackHand|string> $gameSession
         */
        $gameSession = $session->get("blackjack");
        /**
         * @var string $winnerSession
         */
        $winnerSession = $gameSession["winner"];
        if ($winnerSession) {
            return true;
        }

        /**
         * @var BlackJackHand $playerHand
         */
        $playerHand = $gameSession["playerHand"];

        $playerHand->addCard((new DeckOfCards($session, "blackjackCards"))->drawCard()[0]);

        if ($playerHand->getPoints() > 21) {
            $gameSession["winner"] = "dealer";
        }
        $request->getSession()->set("blackjack", $gameSession);
        return true;
    }
    public static function stand(Request $request): bool
    {
        $session = $request->getSession();
        /**
         * @var array<BlackJackHand|string> $gameSession
        */
        $gameSession = $session->get("blackjack");
        /**
         * @var string $winnerSession
         */
        $winnerSession = $gameSession["winner"];
        if ($winnerSession) {
            return true;
        }

        /**
         * @var BlackJackHand $dealerHand
         */
        $dealerHand = $gameSession["dealerHand"];

        /**
         * @var BlackJackHand $playerHand
         */
        $playerHand = $gameSession["playerHand"];

        // unhide first card
        $dealerHand->getCards()[0]->unhide();

        $dealerPoints = $dealerHand->getPoints();
        $playerPoints = $playerHand->getPoints();

        // dealer draws cards until the points equal to 17 or over 17
        while ($dealerPoints < 17) {
            $dealerHand->addCard((new DeckOfCards($session, "blackjackCards"))->drawCard()[0]);
            $dealerPoints = $dealerHand->getPoints();
        }

        if ($dealerPoints > 21) {
            $gameSession["winner"] = "player";
        } elseif ($playerPoints > 21) {
            $gameSession["winner"] = "dealer";
        } elseif ($playerPoints > $dealerPoints) {
            $gameSession["winner"] = "player";
        } elseif ($playerPoints < $dealerPoints) {
            $gameSession["winner"] = "dealer";
        } elseif ($playerPoints === $dealerPoints) {
            $gameSession["winner"] = "push";
        }
        $request->getSession()->set("blackjack", $gameSession);

        return true;
    }
}
