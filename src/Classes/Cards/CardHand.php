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
        if ($index === null) {
            return $this->hand;
        }

        if (!isset($this->hand[$index])) {
            throw new Exception('Card not found');
        }
        return [$this->hand[$index]];
    }

    /**
     * returns all cards names from the player's hand.
     *
     * @return array<mixed> The card.
     * @throws Exception if the card is not found in the hand.
    */
    public function getCardNames(): array
    {
        $hand = [];

        foreach ($this->hand as $card) {
            array_push($hand, $card->getName());
        }

        return $hand;
    }

    /**
     * Adds a card to the player's hand.
     *
     * @param Card $card The card to add.
    */
    public function addCard(Card $card): void
    {
        array_push($this->hand, $card);
        $this->hand = array_values($this->hand);
    }

    /**
     This method removes and returns a card from the player's hand at the specified index.
     It throws an exception if the card is not found in the hand.
     * @param int $index The index of the card to remove.
     * @return Card|array<Card> The removed card as a Card object, or an array of Cards if multiple cards were drawn.
     * @throws Exception If the card is not found in the hand.
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

    /**
     Draws a card by providing its name
     * @param string $cardName The name of card
     * @return Card<Card> The removed card as a Card object, or an array of Cards if multiple cards were drawn.
     * @throws Exception If the card is not found in the hand. or if Internal Error.
     */
    public function drawCardByName(string $cardName): array | Card
    {
        $index = $this->getByName($cardName, true);

        if (!is_int($index)) {
            throw new Exception('Internal Error');
        }
        if (!isset($this->hand[$index])) {
            throw new Exception('Card not found');
        }
        $result = $this->hand[$index];
        array_splice($this->hand, $index, 1);
        return $result;
    }


    /**
     * Find and return card from hand by its name.
     * @param string $name Name to find
     * @param boolean $getIndex If true, return index else return the card object
     * @return Card|int|null It returns card if $getIndex param is set to false,
     * else it returns the index of the card. if not found, it returns null
     */
    public function getByName(string $name, bool $getIndex = false): Card | int | null
    {
        foreach ($this->hand as $key => $val) {
            if ($val->getName() == $name) {
                if ($getIndex) {
                    return $key;
                }

                return $val;
            }
        }
        return null;
    }

    /**
     * Find and return all cards from hand with same name.
     * @param string $name Name to find
     * @param boolean $getIndex If true, return index else return the card object
     * @return array<Card>|array<int> It returns array of cards if $getIndex param is set to false,
     * else it returns array of card indexes .
     */
    public function getAllByName(string $name, bool $getIndex = false): array
    {
        $res = [];

        foreach ($this->hand as $key => $val) {
            if ($val->getName() == $name) {
                if ($getIndex) {
                    array_push($res, (int)$key);
                }
                if (!$getIndex) {
                    array_push($res, $val);
                }

            }
        }
        return $res;
    }

    public function hideCards(): void
    {
        foreach ($this->hand as $card) {
            $card->hide();
        }
    }
    public function unhideCards(): void
    {
        foreach ($this->hand as $card) {
            $card->unhide();
        }
    }
}
