<?php

namespace App\Classes\BlackJackGame;

use App\Classes\Cards\Card;
use PHPUnit\Framework\TestCase;

/**
 * Test casses for class BlackJackHand
 */

class BlackJackHandTest extends TestCase
{
    /**
     * Constuct object and verify that the object has the expected properties.
     */
    public function testCreateHand(): void
    {
        $hand = new BlackJackHand();
        $this->assertInstanceOf("\App\Classes\Cards\CardHand", $hand);
    }

    /**
     * Get points from hand
     */
    public function testHandGetPoints(): void
    {
        $hand = new BlackJackHand();
        $this->assertEquals($hand->getPoints(), 0);

        $card1 = new Card("2", "hearts");
        $card2 = new Card("A", "diamonds");
        $card3 = new Card("A", "diamonds");
        $hand->addCard($card1);
        $hand->addCard($card2);
        $this->assertEquals($hand->getPoints(), 13);
        $hand->addCard($card3);
        $this->assertEquals($hand->getPoints(), 14);

    }
}