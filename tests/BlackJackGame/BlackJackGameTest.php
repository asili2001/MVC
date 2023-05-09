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
    public function testStartGame()
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
    public function testHit()
    {
        $blackjack = new Game([]);
        $blackjack->hit();
        // check if playerHand got 3 cards
        $playerHand = $blackjack->getGameData()["playerHand"];
        $this->assertEquals(count($playerHand->getCards()), 3);

        // we make more hits to get more then 21 points.
        $blackjack->hit();
        $blackjack->hit();
        $blackjack->hit();
        $blackjack->hit();

        $playerHand = $blackjack->getGameData()["playerHand"];
        $this->assertTrue($playerHand->getPoints() > 21);
    }

    /**
     * test stand() that gives the dealr cards untile the total of points will be 17 or above.
     */
    public function testStand()
    {
        // setup multible hands for testing
        $CardAoD = new Card("A", "diamonds");
        $Card2oD = new Card("2", "diamonds");
        $CardKoD = new Card("K", "diamonds");
        $CardKoH = new Card("K", "hearts");
        $Card7oH = new Card("7", "hearts");

        $winningHand = new BlackJackHand();
        $losingHand = new BlackJackHand();
        $bigPointsHand = new BlackJackHand();
        $smallPointsHand = new BlackJackHand();

        $smallPointsHand->addCard($Card2oD);

        $bigPointsHand->addCard($CardKoH);
        $bigPointsHand->addCard($CardKoH);
        $bigPointsHand->addCard($Card2oD);


        $winningHand->addCard($CardAoD);
        $winningHand->addCard($CardKoD);
        $winningHand->addCard($CardKoH);

        $losingHand->addCard($CardKoD);
        $losingHand->addCard($Card7oH);

        $winningPlayerGameArr1 = [
            "playerHand" => $winningHand,
            "dealerHand" => $losingHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $winningDealerGameArr1 = [
            "playerHand" => $losingHand,
            "dealerHand" => $winningHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $winningPlayerGameArr2 = [
            "playerHand" => $winningHand,
            "dealerHand" => $bigPointsHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $winningDealerGameArr2 = [
            "playerHand" => $bigPointsHand,
            "dealerHand" => $winningHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $pushGameArr = [
            "playerHand" => $winningHand,
            "dealerHand" => $winningHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];

        $inGameGameArr1 = [
            "playerHand" => $losingHand,
            "dealerHand" => $losingHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];
        $inGameGameArr2 = [
            "playerHand" => $losingHand,
            "dealerHand" => $smallPointsHand,
            "deck" => new DeckOfCards(),
            "winner" => null
        ];

        $endedGameArr = [
            "playerHand" => $losingHand,
            "dealerHand" => $losingHand,
            "deck" => new DeckOfCards(),
            "winner" => "player"
        ];


        // check if game already has been ended
        $blackjack = new Game($endedGameArr);
        $blackjack->stand();
        $this->assertFalse(is_null($blackjack->getGameData()["winner"]));

        $blackjack = new Game($inGameGameArr1);
        $blackjack->stand();

        // check if first card in dealerHand is unhidden
        $dealerHand = $blackjack->getGameData()["dealerHand"];
        $this->assertFalse($dealerHand->getCards()[0]->isHidden(), $dealerHand->getCards()[0]->isHidden());

        // check stand on winning player with points of 21
        $blackjack = new Game($winningPlayerGameArr1);
        $blackjack->stand();
        $this->assertEquals($blackjack->getGameData()['winner'], "player");

        // check stand on winning player with dealer having points of more then 21
        $blackjack = new Game($winningPlayerGameArr2);
        $blackjack->stand();
        $this->assertEquals($blackjack->getGameData()['winner'], "player");

        // check stand on winning dealer with points of 21
        $blackjack = new Game($winningDealerGameArr1);
        $blackjack->stand();
        $this->assertEquals($blackjack->getGameData()['winner'], "dealer");

        // check stand on winning dealer with dealer having points of more then 21
        $blackjack = new Game($winningDealerGameArr2);
        $blackjack->stand();
        $this->assertEquals($blackjack->getGameData()['winner'], "dealer");

        // check stand on push
        $blackjack = new Game($pushGameArr);
        $blackjack->stand();
        $this->assertEquals($blackjack->getGameData()['winner'], "push");

        // check if dealer gets extra cards if total of points is lower then 17
        $blackjack = new Game($inGameGameArr2);
        $dealerHandPointsBefore = ($blackjack->getGameData()["dealerHand"])->getPoints();
        $blackjack->stand();
        $dealerHandPointsAfter = ($blackjack->getGameData()["dealerHand"])->getPoints();
        $this->assertNotEquals($dealerHandPointsBefore, $dealerHandPointsAfter);
    }
}
