<?php

namespace App\Classes;

use Symfony\Component\Config\Definition\Exception\Exception;

class CardHand
{
    /**
     * @var array<Card> $hand
    */
    private $hand = [];

    /**
     * @param array<Card> $cards
    */
    public function __construct(array $cards = [])
    {
        $count = count($cards);
        for ($i=0; $i < $count - 1; $i++) {
            $this->hand = $cards;
        }
    }

    /**
     * returns a card from the player's hand at the specified index.
     *
     * @param int $index The index of the card to return.
     * @return array<Card> The card.
     * @throws Exception if the card is not found in the hand.
    */
    public function getCards(?int $index = null): array
    {
        if ($index == null) {
            return $this->hand;
        }

        if (!isset($this->hand[$index])) {
            throw new Exception('Card not found');
        }
        return [$this->hand[$index]];
    }

    /**
     * Adds a card to the player's hand.
     *
     * @param Card $card The card to add.
    */
    public function addCard(Card $card): void
    {

        $this->hand[] = $card;
    }

    /**
     * Removes and returns a card from the player's hand at the specified index.
     *
     * @param int $index The index of the card to remove.
     * @return array<Card>|Card The removed card, either as an array or a Card object.
     * @throws Exception if the card is not found in the hand.
    */
    public function drawCard(int $index): array | Card
    {
        if (!isset($this->hand[$index])) {
            throw new Exception('Card not found');
        }
        $result = $this->hand[$index];
        array_splice($this->hand, $index, 1);
        return $result;
    }
}
