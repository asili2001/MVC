<?php

namespace App\Classes;

use Symfony\Component\Config\Definition\Exception\Exception;

class CardHand
{
    private $hand = [];
    public function __construct(array $cards)
    {
        for ($i=0; $i < count($cards) - 1; $i++) {
            $this->hand = $cards;
        }
    }

    public function getCards($index = null)
    {
        if ($index == null) {
            return $this->hand;
        }

        if (!isset($this->hand[$index])) {
            throw new Exception('Card not found');
        }
        return [$this->hand[$index]];
    }

    public function addCard($card)
    {

        $this->hand[] = $card;
    }

    public function drawCard($index)
    {
        if (!isset($this->hand[$index])) {
            throw new Exception('Card not found');
        }
        $result = $this->hand[$index];
        array_splice($this->hand, $index, 1);
        return $result;
    }
}
