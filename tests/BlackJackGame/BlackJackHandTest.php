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
    public function testCreateHand()
    {
        $hand = new BlackJackHand();
        $this->assertInstanceOf("\App\Classes\Cards\CardHand", $hand);
    }

    /**
     * Get points from hand
     */
    public function testHandGetPoints()
    {
        $hand = new BlackJackHand();
        $this->assertEquals($hand->getPoints(), 0);

        $Card1 = new Card("2", "hearts");
        $Card2 = new Card("A", "diamonds");
        $Card3 = new Card("A", "diamonds");
        $hand->addCard($Card1);
        $hand->addCard($Card2);
        $this->assertEquals($hand->getPoints(), 13);
        $hand->addCard($Card3);
        $this->assertEquals($hand->getPoints(), 14);

    }
}