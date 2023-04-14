<?php

namespace App\Classes;

class Card
{
    protected $allowedNames = ["joker", "A", 2, 3, 4, 5, 6, 7, 8, 9, 10, "J", "Q", "K"];
    protected $allowedSymbols = ["hearts", "diamonds", "spades", "clubs", "joker"];
    protected $name = null;
    protected $symbol = null;
    public function __construct($name, $symbol)
    {
        if (!in_array($name, $this->allowedNames) || !in_array($symbol, $this->allowedSymbols)) {
            die("Name Or Symbol Not Allowed");
        }
        $this->name = $name;
        $this->symbol = $symbol;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getSymbol(): string
    {
        return $this->symbol;
    }
}
