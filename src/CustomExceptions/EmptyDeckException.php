<?php

namespace App\CustomExceptions;

use Symfony\Component\Config\Definition\Exception\Exception;

class EmptyDeckException extends Exception
{
    public function errorMessage()
    {
        //error message
        $errorMsg = $this->getMessage() ?? "The deck is empty";
        return $errorMsg;
    }
}
