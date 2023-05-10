<?php

namespace App\Classes\Cards;
use PHPUnit\Framework\TestCase;


class CardGraphicTest extends TestCase
{
    /**
    *  Constuct object and verify that the object has the expected properties.
    */
    public function testCreateCard()
    {
        // creating card
        $card = new Card("4", "hearts");
        $this->assertNotNull($card->getName());
    }

    /**
     * Getting representation of a card
     */
    public function testGetRepresentation()
    {
        $card = new CardGraphic("4", "hearts");
        $this->assertEquals($card->getRepresentation(), "hearts.svg");
    }
}