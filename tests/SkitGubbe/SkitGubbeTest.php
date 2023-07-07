<?php

namespace App\Classes\SkitGubbe;

use App\Classes\Cards\Card;
use Exception;
use PHPUnit\Framework\TestCase;

class SkitGubbeTest extends TestCase
{
    /**
     * Constuct object and verify that the object has the expected properties.
     */
    public function testStartGame(): void
    {
        // First, Create a new game with no game data.
        // It will generate a game array with playerHand, dealerHand, deck and winner(null)
        $skitgubbe = new Game([]);
        $this->assertInstanceOf("\App\Classes\SkitGubbe\Game", $skitgubbe);
        $this->assertFalse(is_null($skitgubbe->getGameData()["computerHand"]));
        $this->assertFalse(is_null($skitgubbe->getGameData()["playerHand"]));
        $this->assertFalse(is_null($skitgubbe->getGameData()["computerVisibleCards"]));
        $this->assertFalse(is_null($skitgubbe->getGameData()["computerHiddenCards"]));
        $this->assertFalse(is_null($skitgubbe->getGameData()["playerVisibleCards"]));
        $this->assertFalse(is_null($skitgubbe->getGameData()["playerHiddenCards"]));
        $this->assertFalse(is_null($skitgubbe->getGameData()["deck"]));
        $this->assertTrue(is_null($skitgubbe->getGameData()["isWinner"]));
        // check if first card in dealerHand is hidden

        /**
         * @var SkitGubbeHand $computerHiddenCards
         */

        $computerHiddenCards = $skitgubbe->getGameData()["computerHiddenCards"];
        print_r($computerHiddenCards->getCards());
        $this->assertTrue(($computerHiddenCards->getCards(0))[0]->isHidden());

        // create a new game using the game array generated from $blackjack.
        // check if the data is equal.
        $skitGubbeGameData = new Game($skitgubbe->getGameData());
        $this->assertInstanceOf("\App\Classes\SkitGubbe\Game", $skitGubbeGameData);
        $this->assertEquals($skitGubbeGameData->getGameData(), $skitgubbe->getGameData());
        // create a new game with only 22 cards (52 - 30)
        $skitGubbeGameData = new Game([], 30);
        $this->assertInstanceOf("\App\Classes\SkitGubbe\Game", $skitGubbeGameData);

        $cardCount = count($skitGubbeGameData->getGameData()["deck"]?->getCards() ?? []);
        // the reason we are verifing its 4 is because the other 18 cards has been dealed between the computer and the user.
        // 6 in floor (computer), 6 in floor (player), 3 in computer hand and 3 in player hand. total is 18
        $this->assertEquals($cardCount, 4);
    }

    public function testGetGameData(): void
    {
        $skitgubbe = new Game([]);
        $this->assertInstanceOf("\App\Classes\SkitGubbe\SkitGubbeHand", $skitgubbe->getGameData()["computerHand"]);
    }

    public function testDiscard(): void
    {
        $skitgubbe = new Game([]);
        $cardA = new Card("A", "hearts");

        // discard without fill
        $skitgubbe->discard("playerHand", 0, false);
        $playerHandCardCount = count($skitgubbe->getGameData()["playerHand"]?->getCards() ?? []);

        $this->assertEquals($playerHandCardCount, 2);

        $skitgubbe->getGameData()["playerHand"]->addCard($cardA);

        $playerHandFirstCard = $skitgubbe->getGameData()["playerHand"]->getCards(0)[0]->getName();

        $this->assertEquals($skitgubbe->getGameData()["playerHand"]->getCards(2)[0]->getName(), "A");

        $skitgubbe->discard("playerHand", 2, true);
        
        $playerHandFirstCardAfter = $skitgubbe->getGameData()["playerHand"]->getCards(0)[0]->getName();

        // check if first card is same
        $this->assertEquals($playerHandFirstCard, $playerHandFirstCardAfter);

        // try draw undefined card
        $this->expectException(Exception::class);
        $skitgubbe->getGameData()["playerHand"]->getCards(3);

    }

    public function testCardExists(): void
    {
        $skitgubbe = new Game([]);
        $cardA = new Card("A", "hearts");
        $skitgubbe->getGameData()["playerHand"]->addCard($cardA);

        $this->assertTrue($skitgubbe->cardExists("playerHand", 3));

        $this->assertFalse($skitgubbe->cardExists("playerHand", 4));


    }

    public function testUsePlayerFloor(): void
    {
        $skitgubbe = new Game([]);

        // player with visible cards
        $playerVisibleFirstCard = $skitgubbe->getGameData()["playerVisibleCards"]->getCards(0)[0]->getName();

        $skitgubbe->usePlayerFloor("playerHand", 0, true);

        $playerHandLastCard = $skitgubbe->getGameData()["playerHand"]->getCards(3)[0]->getName();

        $this->assertEquals($playerVisibleFirstCard, $playerHandLastCard);
    
        // player with hidden cards
        $playerHiddenFirstCard = $skitgubbe->getGameData()["playerHiddenCards"]->getCards(0)[0]->getName();

        $skitgubbe->usePlayerFloor("playerHand", 0, false);

        $playerHandLastCard = $skitgubbe->getGameData()["playerHand"]->getCards(4)[0]->getName();

        $this->assertEquals($playerHiddenFirstCard, $playerHandLastCard);

        // computer, but use hidden cards
        $computerVisibleFirstCard = $skitgubbe->getGameData()["computerHiddenCards"]->getCards(0)[0]->getName();

        $skitgubbe->usePlayerFloor("computerHand", 0, false);

        $computerHandLastCard = $skitgubbe->getGameData()["computerHand"]->getCards(3)[0]->getName();

        $this->assertEquals($computerVisibleFirstCard, $computerHandLastCard);

    }

    public function testAddToFloor(): void
    {
        // Add a card
        $skitGubbe = new Game([]);
        $card3 = new Card("3", "hearts");
        $card5 = new Card("5", "hearts");
        $card10 = new Card("10", "hearts");
        $card2 = new Card("2", "hearts");
        $addCard5 = $skitGubbe->addToFloor($card5, "playerHand");

        $this->assertEquals($addCard5, "ADDED_TO_FLOOR");

        // add 4 same cards
        $skitGubbe = new Game([]);
        $skitGubbe->addToFloor($card5, "playerHand");
        $skitGubbe->addToFloor($card5, "playerHand");
        $skitGubbe->addToFloor($card5, "playerHand");
        $addCard5 = $skitGubbe->addToFloor($card5, "playerHand");

        $this->assertEquals($addCard5, "CLEAR_FLOOR");
        // Check if the cards is moved to the basket array
        /**
         * @var SkitGubbeHand $basket;
         */
        $basket = $skitGubbe->getGameData()["basket"];
        $skitGubbeBasket = $basket->getCardNames();
        $expectedArr = ["5", "5", "5", "5"];
        
        $this->assertEquals(array_diff($skitGubbeBasket, $expectedArr), []);
        
        // add a 10 card to clear the floor. No need for new Game Object
        $skitGubbe->addToFloor($card5, "playerHand");
        $skitGubbe->addToFloor($card5, "playerHand");
        $addCard10 = $skitGubbe->addToFloor($card10, "playerHand");
        
        $this->assertEquals($addCard10, "CLEAR_FLOOR");
        
        // Check if the cards is moved to the basket array
        /**
         * @var SkitGubbeHand $basket;
         */
        $basket = $skitGubbe->getGameData()["basket"];
        $skitGubbeBasket = $basket->getCardNames();
        $expectedArr = ["5", "5", "5", "5", "5", "5", "10"];

        $this->assertEquals(array_diff($skitGubbeBasket, $expectedArr), []);

        // add a 2 card. check if right message returned
        $addCard2 = $skitGubbe->addToFloor($card2, "playerHand");

        $this->assertEquals($addCard2, "ANOTHER_CARD");

        // add a smaller card. check if the cards will move from floor to the player hand
        $skitGubbe = new Game([]);
        $skitGubbe->addToFloor($card5, "playerHand");
        $addCard3 = $skitGubbe->addToFloor($card3, "playerHand");

        $this->assertEquals($addCard3, "WEAK_CARD");

        // check cards in player hand
        /**
         * @var SkitGubbeHand $playerHand;
         */
        $playerHand = $skitGubbe->getGameData()["playerHand"];
        $skitGubbePlayerHand = $playerHand->getCardNames();

        // 3 already in hand plus the 2 cards we've added (5, 3) equal to 5
        $this->assertEquals(count($skitGubbePlayerHand), 5);

    }

    public function testAvailability(): void
    {
        $skitGubbe = new Game([]);

        $this->assertSame(array_diff($skitGubbe->availability("player"), [false, false, false]), []);

        // remove all cards from the player visible cards and try again
        $skitGubbe->usePlayerFloor("playerHand", 0, true);
        $skitGubbe->usePlayerFloor("playerHand", 0, true);
        $skitGubbe->usePlayerFloor("playerHand", 0, true);

        $this->assertSame($skitGubbe->availability("player"), [false, true, false]);

        // remove all cards from the player hidden cards and try again
        $skitGubbe->usePlayerFloor("playerHand", 0, false);
        $skitGubbe->usePlayerFloor("playerHand", 0, false);
        $skitGubbe->usePlayerFloor("playerHand", 0, false);

        $this->assertSame($skitGubbe->availability("player"), [false, true, true]);

    }

    public function testCheckWinner(): void
    {
        $skitGubbe = new Game([]);

        // make player win by removing all cards from hand, visible and hidden.
        // visible
        $skitGubbe->usePlayerFloor("playerHand", 0, true);
        $skitGubbe->usePlayerFloor("playerHand", 0, true);
        $skitGubbe->usePlayerFloor("playerHand", 0, true);

        // hidden
        $skitGubbe->usePlayerFloor("playerHand", 0, false);
        $skitGubbe->usePlayerFloor("playerHand", 0, false);
        $skitGubbe->usePlayerFloor("playerHand", 0, false);

        // hand
        $handCards = $skitGubbe->getGameData()["playerHand"]->getCards();
        foreach ($handCards as $_) {
            $skitGubbe->getGameData()["playerHand"]->drawCard(0);
        }

        $this->assertSame($skitGubbe->availability("player"), [true, true, true]);

        $skitGubbe->checkWinner();

        $this->assertTrue($skitGubbe->getGameData()["isWinner"]);

        // make computer win
        // visible
        $skitGubbe->usePlayerFloor("computerHand", 0, true);
        $skitGubbe->usePlayerFloor("computerHand", 0, true);
        $skitGubbe->usePlayerFloor("computerHand", 0, true);

        // hidden
        $skitGubbe->usePlayerFloor("computerHand", 0, false);
        $skitGubbe->usePlayerFloor("computerHand", 0, false);
        $skitGubbe->usePlayerFloor("computerHand", 0, false);

        // hand
        $handCards = $skitGubbe->getGameData()["computerHand"]->getCards();
        foreach ($handCards as $_) {
            $skitGubbe->getGameData()["computerHand"]->drawCard(0);
        }

        // add a card to player hand
        $cardA = new Card("A", "hearts");
        $skitGubbe->getGameData()["playerHand"]->addCard($cardA);

        // check winner again
        $skitGubbe->checkWinner();

        $this->assertFalse($skitGubbe->getGameData()["isWinner"]);
        
    }


    public function testSetMessage(): void
    {
        $skitGubbe = new Game([]);

        $skitGubbe->setMessage("Hello World");

        $this->assertEquals($skitGubbe->getGameData()["message"], "Hello World");
    }
    public function testFillHand(): void
    {
        $skitGubbe = new Game([]);

        // discard a card
        $skitGubbe->discard("playerHand", 0, false);
        $this->assertEquals(count($skitGubbe->getGameData()["playerHand"]->getCards()), 2);
        $skitGubbe->fillHand("playerHand");
        $this->assertEquals(count($skitGubbe->getGameData()["playerHand"]->getCards()), 3);
    }
}
