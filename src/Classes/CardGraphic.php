<?php

namespace App\Classes;

class CardGraphic extends Card
{
    private $representation = [
        "hearts" => "hearts.svg",
        "diamonds" => "diamonds.svg",
        "spades" => "spades.svg",
        "clubs" => "clubs.svg",
        "joker" => "joker.svg",
    ];
    public function __construct($name, $symbol)
    {
        parent::__construct($name, $symbol);
    }

    public function getRepresentation(): string
    {
        return $this->representation[$this->symbol];
    }
}
