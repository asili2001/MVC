<?php

namespace App\Classes\Cards;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\Exception;

class CardTest extends TestCase
{
   /**
    *  Constuct object and verify that the object has the expected properties.
    */
    public function testCreateCard(): void
    {
        $cardName = "5";
        $cardSymbol = "hearts";
        $fakeCardName = "15";
        $fakeCardSymbol = "rocks";

        // creating card
        $card = new Card($cardName, $cardSymbol);
        $this->assertNotNull($card->getName());

        // creating card with fake name and symbol
        $this->expectException(Exception::class);
        new Card($fakeCardName, $fakeCardSymbol);

    }

    /**
     * getting name of card
     */
    public function testGetName(): void
    {
        $card = new Card("2", "diamonds");
        $this->assertEquals($card->getName(), "2");
    }

    /**
     * getting name of card
     */
    public function testSymbol(): void
    {
        $card = new Card("2", "diamonds");
        $this->assertEquals($card->getSymbol(), "diamonds");
    }

    /**
     * Test hide card
     */
    public function testHideCard(): void
    {
        $card = new Card("2", "diamonds");
        $this->assertFalse($card->isHidden());
        $card->hide();
        $this->assertTrue($card->isHidden());
        $card->unhide();
        $this->assertFalse($card->isHidden());
    }

}