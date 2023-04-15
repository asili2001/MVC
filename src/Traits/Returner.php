<?php

namespace App\Traits;

trait Returner
{
    public function ArrReturner(mixed $data, int $statusCode = -1, string $message = "", bool $isJson = false): array
    {
        if ($isJson) {
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
