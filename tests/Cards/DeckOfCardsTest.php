<?php

namespace App\Classes\Cards;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\Exception;
use App\CustomExceptions\EmptyDeckException;

class DeckOfCardsTest extends TestCase
{
    /**
     * Construct Object and verify that the object has the expected values
     */
    public function testCreateDeckOfCard(): void
    {
        // deck with no payload
        $deck = new DeckOfCards();
        $this->assertEquals(count($deck->getCards()), 52);

        // deck with the data of the previus deck
        $deck = new DeckOfCards($deck->getCards());
        $this->assertInstanceOf("App\Classes\Cards\DeckOfCards", $deck);
        $this->assertIsArray($deck->getCards());
        $this->assertInstanceOf("App\Classes\Cards\Card", $deck->getCards()[0]);
    }

    /**
     * test add jokers to the deck
     * reset the deck
     */
    public function testAddJokersAndResetDeck(): void
    {
        $deck = new DeckOfCards();
        // check total of cards without jokers
        $this->assertEquals(count($deck->getCards()), 52);
        $deck->hasJokers();
        $deck->resetCards();
        // check total of cards with jokers
        $this->assertEquals(count($deck->getCards()), 54);
    }

    /**
     * test shuffle deck
     */
    public function testShuffleDeck(): void
    {
        $deck = new DeckOfCards();
        // check if the first card is A
        $this->assertTrue(($deck->getCards()[0])->getName() === "A" && ($deck->getCards()[51])->getName() === "K");
        // shuffle
        $deck->shuffleCards();
        // check if the first card is not A
        $this->assertFalse(($deck->getCards()[0])->getName() === "A" && ($deck->getCards()[51])->getName() === "K");
    }

    /**
     * test sort deck
     */
    public function testSortDeck(): void
    {
        $deck = new DeckOfCards();
        // shuffle
        $deck->shuffleCards();
        // check if the first card is A
        $this->assertFalse(($deck->getCards()[0])->getName() === "A" && ($deck->getCards()[51])->getName() === "K");
        // sort
        $deck->sortCards();
        // check if the first card is A
        $this->assertTrue(($deck->getCards()[0])->getName() === "A" && ($deck->getCards()[51])->getName() === "K");

    }

    /**
     * test deaw card
     */
    public function testDrawCard(): void
    {
        $deck = new DeckOfCards();
        // check total of cards is 52
        $this->assertEquals(count($deck->getCards()), 52);
        $deck->drawCard();
        // check total of cards is 51
        $this->assertEquals(count($deck->getCards()), 51);
        // test draw more cards then what there is
        $this->expectException(EmptyDeckException::class);
        $deck->drawCard(55);

    }

    /**
     * test deal cards
     */
    public function testDealCards(): void
    {
        $deck = new DeckOfCards();
        // check total of cards is 52
        $this->assertEquals(count($deck->getCards()), 52);
        $dealRes = $deck->dealCards(2, 10);
        // check if every player got 5 cards
        $this->assertEquals(count($dealRes['player 1']->getCards()), 5);
        $this->assertEquals(count($dealRes['player 2']->getCards()), 5);
        // check total of cards is 42
        $this->assertEquals(count($deck->getCards()), 42);
        // test deal more cards then what there is
        $deck = new DeckOfCards();
        $this->expectException(EmptyDeckException::class);
        $dealRes = $deck->dealCards(2, 60);

    }
    
    public function testDealThreeCardsToZeroPlayers(): void
    {
        // test deal one card to 0 players
        $deck = new DeckOfCards();
        $this->expectException(Exception::class);
        $deck->dealCards(0, 3);

    }
    public function testDealZeroCardsToThreePlayers(): void
    {
        // test deal one card to 0 players
        $deck = new DeckOfCards();
        $this->expectException(Exception::class);
        $deck->dealCards(3, 0);

    }
}