<?php

namespace App\Classes\Cards;

class CardGraphic extends Card
{
    /**
     * Maps card suits and the joker card to their respective SVG image file names.
     * @var array<string> $representation
    */
    private $representation = [
        "hearts" => "hearts.svg",
        "diamonds" => "diamonds.svg",
        "spades" => "spades.svg",
        "clubs" => "clubs.svg",
        "joker" => "joker.svg",
    ];

    /**
     * Creates a Card
     */
    public function __construct(string $name, string $symbol)
    {
        parent::__construct($name, $symbol);
    }

    /**
     * Returns the representation
     */
    public function getRepresentation(): string
    {
        return $this->representation[$this->symbol];
    }
}
