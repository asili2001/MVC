<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckyController extends AbstractController
{
    #[Route('/lucky', name: 'lucky')]
    public function lucky(): Response
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $randomDay = $days[random_int(0, 6)];

        $data = [
            'luckyDay' => $randomDay
        ];

        return $this->render('lucky.html.twig', $data);
    }
}