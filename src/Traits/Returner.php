<?php

namespace App\Traits;

trait Returner
{
    /**
     * It will put the data to return in a template so all returned data gets same look.
     *
     * @param mixed $data The data to return.
     * @param int $statusCode Status code.
     * @param string $message A custom message.
     * @param bool $isJson Returns json data instead of array.
     * @return array<mixed>|string The result.
    */
    public function arrReturner(bool $isJson, mixed $data, int $statusCode = -1, string $message = ""): array|string
    {
        if ($isJson && gettype($data) === "string") {
            $data = json_decode($data, true);
        }

        $data = [
            "data" => $data,
            "status_code" => $statusCode,
            "message" => $message
        ];

        return $data;
    }
}
