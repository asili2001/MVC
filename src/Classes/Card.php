<?php

namespace App\Classes;

class Card
{
    /**
     * @var array<int|string> $allowedNames
    */
    protected array $allowedNames = ["joker", "A", 2, 3, 4, 5, 6, 7, 8, 9, 10, "J", "Q", "K"];

    /**
     * @var array<string> $allowedSymbols
    */
    protected array $allowedSymbols = ["hearts", "diamonds", "spades", "clubs", "joker"];
    protected ?string $name = null;
    protected ?string $symbol = null;
    protected bool $hidden = false;
    public function __construct(string $name, string $symbol)
    {
        if (in_array($name, $this->allowedNames) && in_array($symbol, $this->allowedSymbols)) {
            $this->name = $name;
            $this->symbol = $symbol;
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }
    public function getSymbol(): ?string
    {
        return $this->symbol;
    }
    public function isHidden(): bool
    {
        return $this->hidden;
    }
    public function hide(): void
    {
        $this->hidden = true;
    }
    public function unhide(): void
    {
        $this->hidden = false;
    }
}
