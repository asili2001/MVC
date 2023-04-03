<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class QuoteControllerJson extends AbstractController
{
    #[Route('/api/quote', name: 'quote')]
    public function quote(): Response
    {
        $quotes = [
            'Livet är som en målning, du är konstnären och du bestämmer färgerna.',
            'Att vara annorlunda är inte en begränsning, det är en superkraft.',
            'Framgång handlar inte om att undvika misstag, det handlar om att lära sig av dem.',
            'Kärlek är som en blomma, den behöver omsorg och uppmärksamhet för att blomstra.',
        ];
        $randomQuote = $quotes[random_int(0, count($quotes) - 1)];

        $data = [
            'quote' => $randomQuote,
            'generationDate' => date("h:i:sa"),
            'date' => date("Y-m-d")
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}