<?php

namespace App\Classes;

use App\CustomExceptions\EmptyDeckException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DeckOfCards
{
    /**
     * @var array<string|int> $cards
     */
    private array $cards = ["A", 2, 3, 4, 5, 6, 7, 8, 9, 10, "J", "Q", "K"];

    /**
     * @var array<string> $symbols
     */
    private array $symbols = ["hearts", "diamonds", "spades", "clubs"];

    /**
     * @var array<Card|CardGraphic> $deck
     */
    private array $deck = array();
    private string $sessionName = "";
    private SessionInterface $session;
    private bool $jokers = false;
    public function __construct(SessionInterface $session, string $sessionName)
    {
        $this->session = $session;
        $this->sessionName = $sessionName;
        /**
         * @var array<Card>|null $deckSession
         */
        $deckSession = $this->session->get($sessionName);
        if (!isset($deckSession)) {
            $this->resetCards();
        }
        if (isset($deckSession)) {
            $this->deck = $deckSession;
        }
    }

    /**
     * Adds joker cards to the deck.
     *
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
        $countsymbols = count($this->symbols);
        $countCards = count($this->cards);
        for ($iSy=0; $iSy < $countsymbols; $iSy++) {
            for ($iCa=0; $iCa < $countCards; $iCa++) {
                $this->deck[] = new CardGraphic(strval($this->cards[$iCa]), strval($this->symbols[$iSy]));
            }
        }

        $this->saveToSession();
    }

    /**
     * Saves the deck to the session
     *
    */
    private function saveToSession(): void
    {
        $this->session->set($this->sessionName, $this->deck);
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
        $this->saveToSession();
    }

    /**
     * Sorts the cards in the deck
     *
    */
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
        $result = array_merge($result, $this->deck);

        $this->deck = $result;
        $this->saveToSession();
    }

    /**
     * Removes and returns cards from the deck.
     *
     * @param int $nrOfCards number of cards to remove.
     * @return array<Card> The removed cards.
     * @throws EmptyDeckException if there is not enaugh cards.
    */
    public function drawCard(int $nrOfCards = 1): array
    {
        if ($nrOfCards > count($this->deck)) {
            throw new EmptyDeckException('There are not enaugh cards');
        }
        $result = [];
        for ($i=1; $i<=$nrOfCards; $i++) {
            if (empty($this->deck)) {
                throw new EmptyDeckException('The deck is empty');
            }
            $cardToDraw = random_int(0, count($this->deck) - 1);
            $result[] = $this->deck[$cardToDraw];

            array_splice($this->deck, $cardToDraw, 1);
        }

        $this->saveToSession();
        return $result;

    }

    /**
     * Removes deals and returns the removed cards with its players.
     *
     * @param int $nrOfPlayers The players to deal the cards with.
     * @param int $nrOfCards The cards to be removed and dealed.
     * @return array<string, CardHand> The players and its cards.
     * @throws EmptyDeckException if there are not enaugh cards
     * @throws Exception if the number of cards cannot be zero or lower
     * @throws Exception if the number of players cannot be zero or lower
    */
    public function dealCards(int $nrOfPlayers, int $nrOfCards): array
    {
        $result = [];
        if ($nrOfCards > count($this->deck)) {
            throw new EmptyDeckException('There are not enaugh cards');
        }
        if ($nrOfCards < 1) {
            throw new Exception('The number of cards cannot be zero or lower');
        }
        if ($nrOfPlayers < 1) {
            throw new Exception('The number of players cannot be zero or lower');
        }
        /**
         * @var int $cardsEachPlayer
         */
        $cardsEachPlayer = $nrOfCards / $nrOfPlayers;

        for ($i=1; $i < $nrOfPlayers + 1; $i++) {
            $result["player {$i}"] = new CardHand($this->drawCard($cardsEachPlayer));
        }
        return $result;

    }
}
