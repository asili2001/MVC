<?php

namespace App\Classes\BlackJackGame;

use App\Classes\Cards\Card;
use App\Classes\Cards\DeckOfCards;
use PHPUnit\Framework\TestCase;

class BlackJackGameTest extends TestCase
{
    /**
     * Constuct object and verify that the object has the expected properties.
     */
    public function testStartGame(): void
    {
        // First, Create a new game with no game data.
        // It will generate a game array with playerHand, dealerHand, deck and winner(null)
        $blackjack = new Game([]);
        $this->assertInstanceOf("\App\Classes\BlackJackGame\Game", $blackjack);
        $this->assertFalse(is_null($blackjack->getGameData()["dealerHand"]));
        $this->assertFalse(is_null($blackjack->getGameData()["playerHand"]));
        $this->assertFalse(is_null($blackjack->getGameData()["deck"]));
        $this->assertTrue(is_null($blackjack->getGameData()["winner"]));
        // check if first card in dealerHand is hidden

        /**
         * @var BlackJackHand $dealerHand
         */
        $dealerHand = $blackjack->getGameData()["dealerHand"];
        $this->assertTrue($dealerHand->getCards()[0]->isHidden());

        // create a new game using the game array generated from $blackjack.
        // check if the data is equal.
        $blackJackPyload = new Game($blackjack->getGameData());
        $this->assertInstanceOf("\App\Classes\BlackJackGame\Game", $blackJackPyload);
        $this->assertEquals($blackJackPyload->getGameData(), $blackjack->getGameData());
    }

    /**
     * Test hit() that takes of a card from the deck and add it to the playerHand
     */
    public function testHit(): void
    {
        $blackjack = new Game([]);
        $blackjack->hit();
        // check if playerHand got 3 cards
        /**
         * @var BlackJackHand $playerHand
         */
        $playerHand = $blackjack->getGameData()["playerHand"];
        $this->assertEquals(count($playerHand->getCards()), 3);

        // we make more hits to get more then 21 points.
        $blackjack->hit();
        $blackjack->hit();
        $blackjack->hit();
        $blackjack->hit();

        /**
         * @var BlackJackHand $playerHand
         */
        $playerHand = $blackjack->getGameData()["playerHand"];
        $this->assertTrue($playerHand->getPoints() > 21);
    }

    /**
     * test stand() that gives the dealr cards untile the total of points will be 17 or above.
     */
    public function testStand(): void
    {
        // setup multible hands for testing
        $cardAOD = new Card("A", "diamonds");
        $card2OD = new Card("2", "diamonds");
        $cardKOD = new Card("K", "diamonds");
        $cardKOH = new Card("K", "hearts");
        $card7OH = new Card("7", "hearts");
        $winningHand = new BlackJackHand();
        $losingHand = new BlackJackHand();
        $bigPointsHand = new BlackJackHand();
        $smallPointsHand = new BlackJackHand();
        $smallPointsHand->addCard($card2OD);
        $bigPointsHand->addCard($cardKOH);
        $bigPointsHand->addCard($cardKOH);
        $bigPointsHand->addCard($card2OD);
        $winningHand->addCard($cardAOD);
        $winningHand->addCard($cardKOD);
        $winningHand->addCard($cardKOH);
        $losingHand->addCard($cardKOD);
        $losingHand->addCard($card7OH);
        $winningPlayer1 = [
            "playerHand" => $winningHand,
            "dealerHand" => $losingHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $winningDealer1 = [
            "playerHand" => $losingHand,
            "dealerHand" => $winningHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $winningPlayer2 = [
            "playerHand" => $winningHand,
            "dealerHand" => $bigPointsHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $winningDealer2 = [
            "playerHand" => $bigPointsHand,
            "dealerHand" => $winningHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $push = [
            "playerHand" => $winningHand,
            "dealerHand" => $winningHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $inGame1 = [
            "playerHand" => $losingHand,
            "dealerHand" => $losingHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $inGame2 = [
            "playerHand" => $losingHand,
            "dealerHand" => $smallPointsHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $ended = [
            "playerHand" => $losingHand,
            "dealerHand" => $losingHand,
            "deck" => new DeckOfCards(),
            "winner" => "player"
        ];
        // check if game already has been ended
        $blackjack = new Game($ended);
        $blackjack->stand();
        $this->assertFalse(is_null($blackjack->getGameData()["winner"]));

        $blackjack = new Game($inGame1);
        $blackjack->stand();

        // check if first card in dealerHand is unhidden
        /**
         * @var BlackJackHand $dealerHand
         */
        $dealerHand = $blackjack->getGameData()["dealerHand"];
        $this->assertFalse($dealerHand->getCards()[0]->isHidden());

        // check stand on winning player with points of 21
        $blackjack = new Game($winningPlayer1);
        $blackjack->stand();
        $this->assertEquals($blackjack->getGameData()['winner'], "player");

        // check stand on winning player with dealer having points of more then 21
        $blackjack = new Game($winningPlayer2);
        $blackjack->stand();
        $this->assertEquals($blackjack->getGameData()['winner'], "player");

        // check stand on winning dealer with points of 21
        $blackjack = new Game($winningDealer1);
        $blackjack->stand();
        $this->assertEquals($blackjack->getGameData()['winner'], "dealer");

        // check stand on winning dealer with dealer having points of more then 21
        $blackjack = new Game($winningDealer2);
        $blackjack->stand();
        $this->assertEquals($blackjack->getGameData()['winner'], "dealer");

        // check stand on push
        $blackjack = new Game($push);
        $blackjack->stand();
        $this->assertEquals($blackjack->getGameData()['winner'], "push");

        // check if dealer gets extra cards if total of points is lower then 17
        $blackjack = new Game($inGame2);
        /**
         * @var BlackJackHand $dealerHand
         */
        $dealerHand = $blackjack->getGameData()["dealerHand"];
        $dealerPointsBefore = $dealerHand->getPoints();
        $blackjack->stand();
        /**
         * @var BlackJackHand $dealerHand
         */
        $dealerHand = $blackjack->getGameData()["dealerHand"];
        $dealerPointsAfter = $dealerHand->getPoints();
        $this->assertNotEquals($dealerPointsBefore, $dealerPointsAfter);
    }
}
