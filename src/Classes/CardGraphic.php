<?php

namespace App\Classes;

class CardGraphic extends Card
{
    /**
     * @var array<string> $representation
    */
    private $representation = [
        "hearts" => "hearts.svg",
        "diamonds" => "diamonds.svg",
        "spades" => "spades.svg",
        "clubs" => "clubs.svg",
        "joker" => "joker.svg",
    ];
    public function __construct(string $name, string $symbol)
    {
        parent::__construct($name, $symbol);
    }

    public function getRepresentation(): string
    {
        return $this->representation[$this->symbol];
    }
}
