<?php

namespace App\Classes;

use Symfony\Component\Config\Definition\Exception\Exception;

class DeckOfCards
{
    private $cards = ["A", 2, 3, 4, 5, 6, 7, 8, 9, 10, "J", "Q", "K"];
    private $symbols = ["hearts", "diamonds", "spades", "clubs"];
    private $deck = [];
    public function __construct($jokers = false)
    {
        if (!isset($_SESSION["playingCards"])) {
            if ($jokers) {
                $this->deck[] = new CardGraphic("joker", "joker");
                $this->deck[] = new CardGraphic("joker", "joker");
            }
            for ($iSy=0; $iSy < count($this->symbols); $iSy++) {
                for ($iCa=0; $iCa < count($this->cards); $iCa++) {
                    $this->deck[] = new CardGraphic($this->cards[$iCa], $this->symbols[$iSy]);
                }
            }
        } else {
            $this->deck = $_SESSION["playingCards"];
        }
    }

    private function saveToSession()
    {
        $_SESSION["playingCards"] = $this->deck;
    }

    public function getCards()
    {
        return $this->deck;
    }

    public function shuffleCards()
    {
        shuffle($this->deck);
        $this->saveToSession();
        return $this->getCards();
    }

    public function sortCards()
    {
        $result = [];

        for ($iSy=0; $iSy < count($this->symbols); $iSy++) {
            for ($iCa=0; $iCa < count($this->cards); $iCa++) {
                for ($x=0; $x < count($this->deck); $x++) {
                    if ($this->deck[$x]->getSymbol() == $this->symbols[$iSy] && $this->deck[$x]->getName() == $this->cards[$iCa]) {
                        $result[] = $this->deck[$x];
                        array_splice($this->deck, $x, 1);
                    }
                }
            }
        }
        $result = array_merge($result, $this->deck);

        $this->deck = $result;
        $this->saveToSession();
    }

    public function drawCard($nrOfCards = 1)
    {
        if ($nrOfCards > count($this->deck)) {
            throw new Exception('There are not enaugh cards');
        }
        $result = [];
        for ($i=1; $i<=$nrOfCards; $i++) {
            $cardToDraw = random_int(0, count($this->deck));
            $result[] = $this->deck[$cardToDraw];
            array_splice($this->deck, $cardToDraw, 1);
        }

        $this->saveToSession();
        return $result;

    }

    public function dealCards(int $nrOfPlayers, int $nrOfCards)
    {
        $result = [];
        if ($nrOfCards > count($this->deck)) {
            throw new Exception('There are not enaugh cards');
        }
        if ($nrOfCards < 1) {
            throw new Exception('The number of cards cannot be zero or lower');
        }
        if ($nrOfPlayers < 1) {
            throw new Exception('The number of players cannot be zero or lower');
        }
        $cardsEachPlayer = $nrOfCards / $nrOfPlayers;

        for ($i=1; $i < $nrOfPlayers + 1; $i++) {
            $result["player {$i}"] = new CardHand($this->drawCard($cardsEachPlayer));
        }
        return $result;

    }
}
