<?php

namespace App\Classes\SkitGubbe;

use App\Classes\Cards\Card;
use App\Classes\Cards\DeckOfCards;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Test casses for class SkitGubbeHand
 */
class SkitGubbeHandTest extends TestCase
{
    /**
     * Get points of card
     */
    public function testCardPoints(): void
    {
        $card9 = new Card("9", "hearts");
        $hand = new SkitGubbeHand();

        $card9Points = $hand->cardPoints($card9);
        $this->assertEquals($card9Points, 7);
    }

    public function testGetNextBigger(): void
    {
        $card9 = new Card("9", "hearts");
        $card3 = new Card("3", "hearts");
        $cardK = new Card("K", "hearts");
        $cardA = new Card("A", "hearts");
        $cardQ = new Card("Q", "hearts");
        $hand = new SkitGubbeHand([$card9, $cardK, $card3]);

        /**
         * @var Card $nextBigger
         */
        $nextBigger = $hand->getNextBigger($cardQ);
        /**
         * @var Card $nextBigger1
         */
        $nextBigger1 = $hand->getNextBigger($card3);
        /**
         * @var Card $nextBigger2
         */
        $nextBigger2 = $hand->getNextBigger($cardA);
        $nextBigger2Index = $hand->getNextBigger($cardA, true);

        
        $this->assertEquals($nextBigger->getName(), "K");
        // check for a card same in hand
        $this->assertEquals($nextBigger1->getName(), "3");
        // if there is no bigger, return first card
        $this->assertEquals($nextBigger2->getName(), "9");
        $this->assertEquals($nextBigger2Index, 0);
    }
}
