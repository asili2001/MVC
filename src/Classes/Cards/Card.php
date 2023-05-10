<?php

namespace App\Classes\Cards;

use Symfony\Component\Config\Definition\Exception\Exception;

class Card
{
    /**
     * Allowed Cards
     * @var array<int|string> $allowedNames
    */
    protected array $allowedNames = ["joker", "A", 2, 3, 4, 5, 6, 7, 8, 9, 10, "J", "Q", "K"];

    /**
     * Allowed symbols of cards
     * @var array<string> $allowedSymbols
    */
    protected array $allowedSymbols = ["hearts", "diamonds", "spades", "clubs", "joker"];
    protected ?string $name = null;
    protected ?string $symbol = null;
    protected bool $hidden = false;

    /**
    This constructor method validates if the provided name and symbol are allowed according to predefined lists, and if so, assigns them to the respective properties of the class.
    @param string $name The name of the card to be validated and saved.
    @param string $symbol The symbol of the card to be validated and saved.
    */
    public function __construct(string $name, string $symbol)
    {
        if (in_array($name, $this->allowedNames) && in_array($symbol, $this->allowedSymbols)) {
            $this->name = $name;
            $this->symbol = $symbol;
            return;
        }
        throw new Exception('This card is not allowed');
    }

    /**
     * Returns the name of the card
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    /**
     * Returns the symbol of the card
     */
    public function getSymbol(): ?string
    {
        return $this->symbol;
    }
    /**
     * Check whether the card is hidden or not.
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * Hide the card
     */
    public function hide(): void
    {
        $this->hidden = true;
    }

    /**
     * Unhide the card
     */
    public function unhide(): void
    {
        $this->hidden = false;
    }
}
