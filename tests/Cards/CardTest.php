<?php

namespace App\Classes\Cards;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\Exception;

class CardTest extends TestCase
{
    // Constuct object and verify that the object has the expected properties.
    public function testCreateCard()
    {
        $cardName = "5";
        $cardSymbol = "hearts";
        $fakeCardName = "15";
        $fakeCardSymbol = "rocks";

        // creating card
        $card = new Card($cardName, $cardSymbol);
        $this->assertEquals($card->getName(), $cardName);

        // creating card with fake name and symbol
        $this->expectException(Exception::class);
        $card = new Card($fakeCardName, $fakeCardSymbol);

    }
}