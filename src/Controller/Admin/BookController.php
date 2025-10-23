<?php
namespace App\Controller\Admin;

use App\Entity\Book;
use App\Form\BookType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/books')]
class BookController extends AbstractController
{
    #[Route('/', name: 'admin_book_index')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 50;

        $query = $em->createQueryBuilder()
            ->select('b', 'a')
            ->from(Book::class, 'b')
            ->leftJoin('b.author', 'a')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $books = $query->getResult();

        $totalBooks = $em->createQueryBuilder()
            ->select('COUNT(b.id)')
            ->from(Book::class, 'b')
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = ceil($totalBooks / $limit);

        return $this->render('admin/books/index.html.twig', [
            'books' => $books,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }


    #[Route('/create', name: 'admin_book_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('admin_book_index');
        }

        return $this->render('admin/books/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_book_edit')]
    public function edit(Request $request, Book $book, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_book_index');
        }

        return $this->render('admin/books/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_book_delete')]
    public function delete(Book $book, EntityManagerInterface $em): Response
    {
        $em->remove($book);
        $em->flush();
        return $this->redirectToRoute('admin_book_index');
    }
}
