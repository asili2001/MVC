<?php

namespace App\Classes\Cards;

use App\CustomExceptions\EmptyDeckException;
use Symfony\Component\Config\Definition\Exception\Exception;

class DeckOfCards
{
    /**
     * The cards that will be existed
     * @var array<string|int> $cards
     */
    private array $cards = ["A", 2, 3, 4, 5, 6, 7, 8, 9, 10, "J", "Q", "K"];


    /**
     * The symbols that will be existed
     * @var array<string> $symbols
     */
    private array $symbols = ["hearts", "diamonds", "spades", "clubs"];

    /**
     * The deck that card will be stored in
     * @var array<Card|CardGraphic> $deck
     */
    private array $deck = array();
    private bool $jokers = false;

    /**
     * This constructor method initializes the DeckOfCards state by
     * either using provided cards data or generating a new set.
     * @param array<Card> $deck
    */
    public function __construct(array $deck = array())
    {
        if (empty($deck)) {
            $this->resetCards();
        }
        if (!empty($deck)) {
            $this->deck = $deck;
        }
    }

    /**
     * Adds joker cards to the deck.
    */
    public function hasJokers(): void
    {
        $this->jokers = true;
    }

    /**
     * Empty out the $deck and start to fill it again with new cards
     *
    */
    public function resetCards(): void
    {
        $this->deck = [];

        if ($this->jokers) {
            $this->deck[] = new CardGraphic("joker", "joker");
            $this->deck[] = new CardGraphic("joker", "joker");
        }

        foreach ($this->symbols as $symbol) {
            foreach ($this->cards as $card) {
                $this->deck[] = new CardGraphic(strval($card), $symbol);
            }
        }
    }

    /**
     * Restruns the deck of cards
     *
     * @return array<Card> The deck
    */
    public function getCards(): array
    {
        return $this->deck;
    }

    /**
     * Shuffles the cards in the deck
     *
    */
    public function shuffleCards(): void
    {
        shuffle($this->deck);
    }

    /**
     * Sorts the cards in the deck
     *
    // */
    public function sortCards(): void
    {
        $result = [];
        $countsymbols = count($this->symbols);
        $countCards = count($this->cards);
        $countDeck = count($this->deck);
        for ($iSy=0; $iSy < $countsymbols; $iSy++) {
            for ($iCa=0; $iCa < $countCards; $iCa++) {
                for ($x=0; $x < $countDeck; $x++) {
                    if ($this->deck[$x]->getSymbol() == $this->symbols[$iSy] && $this->deck[$x]->getName() == $this->cards[$iCa]) {
                        $result[] = $this->deck[$x];
                        array_splice($this->deck, $x, 1);
                        $countDeck = count($this->deck);
                    }
                }
            }
        }
        $this->deck = array_merge($result, $this->deck);
    }

    /**
     * Removes and returns cards from the deck.
     *
     * @param int $nrOfCards number of cards to remove.
     * @return array<Card> The removed cards.
     * @throws EmptyDeckException if there is not enough cards.
    */
    public function drawCard(int $nrOfCards = 1): array
    {
        if ($nrOfCards > count($this->deck)) {
            throw new EmptyDeckException('There are not enough cards');
        }

        $result = array_splice($this->deck, 0, $nrOfCards);
        return $result;
    }

    /**
     * Removes deals and returns the removed cards with its players.
     *
     * @param int $nrOfPlayers The players to deal the cards with.
     * @param int $nrOfCards The cards to be removed and dealed.
     * @return array<string, CardHand> The players and its cards.
     * @throws EmptyDeckException if there are not enough cards
     * @throws Exception if the number of cards cannot be zero or lower
     * @throws Exception if the number of players cannot be zero or lower
    */
    public function dealCards(int $nrOfPlayers, int $nrOfCards): array
    {
        if ($nrOfCards > count($this->deck)) {
            throw new EmptyDeckException('There are not enough cards');
        }
        if ($nrOfCards < 1) {
            throw new Exception('The number of cards cannot be zero or lower');
        }
        if ($nrOfPlayers < 1) {
            throw new Exception('The number of players cannot be zero or lower');
        }

        $cardsEachPlayer = (int)($nrOfCards / $nrOfPlayers);
        $result = [];

        for ($i=1; $i < $nrOfPlayers + 1; $i++) {
            $result["player {$i}"] = new CardHand($this->drawCard($cardsEachPlayer));
        }
        return $result;

    }
}
