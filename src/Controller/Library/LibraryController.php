<?php

namespace App\Controller\Library;

use App\Repository\BooksRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Books;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;

class LibraryController extends AbstractController
{
    #[Route('/library', name: 'app_library')]
    public function index(
        BooksRepository $booksRepository
    ): Response {
        $books = $booksRepository->findAll();
        return $this->render('library/index.html.twig', [
            'books' => $books,
        ]);
    }

    #[Route('/library/book/id/{bookId}', name: 'single_book')]
    public function singleBook(
        BooksRepository $booksRepository,
        int $bookId
    ): Response {
        $book = $booksRepository->find($bookId);
        return $this->render('library/book.html.twig', [
            'book' => $book,
        ]);
    }
    #[Route('/library/reset', name: 'reset_library')]
    public function resetLibrary(
        KernelInterface $kernel
    ): Response {
        $rootPath = $kernel->getProjectDir();
        $backupPath = $rootPath . '/var/backup.db';
        $targetPath = $rootPath . '/var/data.db';

        $filesystem = new Filesystem();
        $filesystem->copy($backupPath, $targetPath, true);

        return $this->redirectToRoute('app_library');
    }

    #[Route('/library/book/create', name: 'book_create', methods: ['POST', 'GET'])]
    public function createBook(
        Request $request,
        ManagerRegistry $doctrine
    ): Response {
        if ($request->isMethod('post')) {
            $name = strval($request->get("name"));
            $author = strval($request->get("author"));
            $isbn = intval($request->get("isbn"));
            $about =strval($request->get("about"));

            $entityManager = $doctrine->getManager();

            $book = new Books();
            $book->setName($name);
            $book->setAuthor($author);
            $book->setIsbn($isbn);
            $book->setAbout($about);

            $entityManager->persist($book);

            $entityManager->flush();

            return $this->redirectToRoute('app_library');
        }

        return $this->render('library/create-book.html.twig');
    }

    #[Route('/library/book/update/{bookId}', name: 'book_update', methods: ['POST', 'GET'])]
    public function updateBook(
        Request $request,
        int $bookId,
        ManagerRegistry $doctrine
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Books::class)->find($bookId);

        if (!$book) {
            throw $this->createNotFoundException(
                'No Book Found With id '. $bookId
            );
        }
        if ($request->isMethod('post')) {
            $name = strval($request->get("name"));
            $author = strval($request->get("author"));
            $isbn = intval($request->get("isbn"));
            $about = strval($request->get("about"));


            if ($book->getName() !== $name) {
                $book->setName($name);
            }
            if ($book->getAuthor() !== $author) {
                $book->setAuthor($author);
            }
            if ($book->getIsbn() !== $isbn) {
                $book->setIsbn($isbn);
            }
            if ($book->getAbout() !== $about) {
                $book->setAbout($about);
            }
            $entityManager->flush();


            return $this->redirectToRoute('app_library');
        }

        return $this->render('library/edit-book.html.twig', [
            "book" => $book
        ]);
    }
    #[Route('/library/book/delete/{bookId}', name: 'book_delete', methods: ['POST', 'GET'])]
    public function deleteBook(
        Request $request,
        int $bookId,
        ManagerRegistry $doctrine
    ): Response {
        $entityManager = $doctrine->getManager();
        $book = $entityManager->getRepository(Books::class)->find($bookId);

        if (!$book) {
            throw $this->createNotFoundException(
                'No Book Found With id '. $bookId
            );
        }
        if ($request->isMethod('post')) {
            $entityManager->remove($book);
            $entityManager->flush();

            return $this->redirectToRoute('app_library');
        }

        return $this->render('library/delete-book.html.twig', [
            "book" => $book
        ]);
    }
}
