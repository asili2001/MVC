<?php

namespace App\Controller\Library;

use App\Repository\BooksRepository;
use App\Util\Returner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;

class JsonLibraryController extends AbstractController
{
    use Returner;
    #[Route('/api/library/books', name: 'json_library_books')]
    public function jsonBooks(
        BooksRepository $booksRepository
    ): Response {
        $books = $booksRepository->findAll();

        $booksData = array();
        foreach ($books as $book) {
            $booksData[] = [
                "id" => $book->getId(),
                "name" => $book->getName(),
                "author" => $book->getAuthor(),
                "isbn" => $book->getIsbn(),
                "about" => $book->getAbout()
            ];
        }

        $res = $this->arrReturner(false, $booksData, 200, "");

        $response = new JsonResponse($res, 200);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }

    #[Route('/api/library/book/{isbn}', name: 'json_single_book')]
    public function jsonSingleBook(
        BooksRepository $booksRepository,
        int $isbn,
        Request $request
    ): Response {
        $book = $booksRepository->findOneBy(['isbn' => $isbn]);

        if (!$book) {
            throw $this->createNotFoundException(
                'No Book Found With isbn '. $isbn
            );
        }

        $bookData = array();
        $bookData[] = [
            "id" => $book->getId(),
            "name" => $book->getName(),
            "author" => $book->getAuthor(),
            "isbn" => $book->getIsbn(),
            "about" => $book->getAbout()
        ];

        $res = $this->arrReturner(false, $bookData, 200, "");

        $response = new JsonResponse($res, 200);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );

        return $response;
    }
}
