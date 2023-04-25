<?php

namespace App\CustomExceptions;

use Symfony\Component\Config\Definition\Exception\Exception;

class EmptyDeckException extends Exception
{
    public function errorMessage(): string|null
    {
        //error message
        if (!$this->getMessage()) {
            return "The deck is empty";
        }

        return $this->getMessage();
    }
}
