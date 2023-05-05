<?php

namespace App\Classes\Cards;

use Symfony\Component\Config\Definition\Exception\Exception;

class CardHand
{
    /**
     * The hand of cards
     * @var array<Card> $hand
    */
    private $hand = [];

    /**
     * This constructor method initializes the hand state by
     * using provided cards data or of just leaving it empty
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
     This method removes and returns a card from the player's hand at the specified index.
     It throws an exception if the card is not found in the hand.
     @param int $index The index of the card to remove.
     @return Card|array<Card> The removed card as a Card object, or an array of Cards if multiple cards were drawn.
     @throws Exception If the card is not found in the hand.
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
