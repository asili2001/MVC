<?php

use App\Classes\Cards\Card;
use App\Classes\Cards\CardHand;
use PHPUnit\Framework\TestCase;

class CardHandTest extends TestCase
{
    /**
    *  Constuct object and verify that the object has the expected properties.
    */
    public function testCreateCardHand(): void
    {
        // test empty hand
        $hand = new CardHand();
        $this->assertInstanceOf("App\Classes\Cards\CardHand", $hand);

        // test hand of cards
        $hand = new CardHand();
        $hand->addCard(new Card("4", "diamonds"));
        $this->assertInstanceOf("App\Classes\Cards\Card", $hand->getCards(0)[0]);
        // $this->expectException(Exception::class);

    }

    // test get cards from hand
    public function testGetCards(): void
    {
        $hand = new CardHand();
        $card = new Card("4", "diamonds");
        $hand->addCard($card);
        $getCard = $hand->getCards(0);
        $this->assertIsArray($hand->getCards());
        $this->assertEquals($getCard, array($card));
        
        // try get undefined card
        $this->expectException(Exception::class);
        $hand->getCards(1)[0]->getName();

    }
    // test drawCard
    public function testDrawCard(): void
    {
        $hand = new CardHand();
        $card = new Card("4", "diamonds");
        $card1 = new Card("5", "diamonds");
        $hand->addCard($card);
        $hand->addCard($card1);
        $this->assertEquals($hand->drawCard(0), $card);
        
        // try draw undefined card
        $this->expectException(Exception::class);
        $hand->drawCard(3);

    }
    // test draw card by name
    public function testDrawCardByName(): void
    {
        $hand = new CardHand();
        $card = new Card("4", "diamonds");
        $card1 = new Card("5", "diamonds");
        $hand->addCard($card);
        $hand->addCard($card1);
        $this->assertEquals(($hand->drawCardByName("5"))->getName(), "5");
        
        // try draw undefined card
        $this->expectException(Exception::class);
        $hand->drawCardByName("6");

    }

    // test get all card that have same name in hand
    public function testgetAllByName(): void
    {
        $hand = new CardHand();
        $card = new Card("4", "diamonds");
        $card1 = new Card("4", "hearts");
        $hand->addCard($card);
        $hand->addCard($card1);
        $this->assertContainsOnly("int", $hand->getAllByName("4", true));
        $this->assertContainsOnly("App\Classes\Cards\Card", $hand->getAllByName("4", false));

    }

    // test hide cards
    public function testhideCards(): void
    {
        $hand = new CardHand();
        $card = new Card("4", "diamonds");
        $card1 = new Card("5", "diamonds");
        $hand->addCard($card);
        $hand->addCard($card1);

        $hand->hideCards();

        $this->assertTrue(($hand->getByName("4"))->isHidden());

    }
    // test unhide cards
    public function testunhideCards(): void
    {
        $hand = new CardHand();
        $card = new Card("4", "diamonds");
        $card1 = new Card("5", "diamonds");
        $hand->addCard($card);
        $hand->addCard($card1);

        $hand->hideCards();
        $hand->unhideCards();

        $this->assertFalse(($hand->getByName("4"))->isHidden());
    }
}