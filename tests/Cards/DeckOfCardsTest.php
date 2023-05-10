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
    public function testCreateDeckOfCard()
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
    public function testAddJokersAndResetDeck()
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
    public function testShuffleDeck()
    {
        $deck = new DeckOfCards();
        // check if the first card is A
        $this->assertEquals(($deck->getCards()[0])->getName(), "A");
        // shuffle
        $deck->shuffleCards();
        // check if the first card is not A
        $this->assertNotEquals(($deck->getCards()[0])->getName(), "A");
    }

    /**
     * test sort deck
     */
    public function testSortDeck()
    {
        $deck = new DeckOfCards();
        // shuffle
        $deck->shuffleCards();
        // check if the first card is A
        $this->assertNotEquals(($deck->getCards()[0])->getName(), "A");
        // sort
        $deck->sortCards();
        // check if the first card is A
        $this->assertEquals(($deck->getCards()[0])->getName(), "A");

    }

    /**
     * test deaw card
     */
    public function testDrawCard()
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
}