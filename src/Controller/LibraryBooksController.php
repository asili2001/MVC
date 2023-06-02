<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LibraryBooksController extends AbstractController
{
    #[Route('/library/books', name: 'app_library_books')]
    public function index(): Response
    {
        return $this->render('library_books/index.html.twig', [
            'controller_name' => 'LibraryBooksController',
        ]);
    }
}
