<?php
namespace App\Controller\Admin;

use App\Entity\Author;
use App\Form\AuthorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class AuthorController extends AbstractController
{
    #[Route('/', name: 'admin_author_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $authors = $em->createQueryBuilder()
            ->select('a', 'COUNT(b.id) AS book_count')
            ->from(Author::class, 'a')
            ->leftJoin('a.books', 'b')
            ->groupBy('a.id')
            ->getQuery()
            ->getResult();

        return $this->render('admin/authors/index.html.twig', [
            'authors' => $authors
        ]);
    }

    #[Route('/create', name: 'admin_author_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute('admin_author_index');
        }

        return $this->render('admin/authors/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_author_edit')]
    public function edit(Request $request, Author $author, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_author_index');
        }

        return $this->render('admin/authors/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_author_delete')]
    public function delete(Author $author, EntityManagerInterface $em): Response
    {
        $em->createQuery('DELETE FROM App\Entity\Book b WHERE b.author = :author')
            ->setParameter('author', $author)
            ->execute();

        $em->remove($author);
        $em->flush();

        return $this->redirectToRoute('admin_author_index');
    }

    #[Route('/{id}/books', name: 'admin_author_books')]
    public function showBooks(
        ?Author $author,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        if (!$author) {
            return $this->redirectToRoute('admin_author_index');
        }
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 100;

        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from(\App\Entity\Book::class, 'b')
            ->where('b.author = :author')
            ->setParameter('author', $author)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $books = $qb->getQuery()->getResult();

        $totalBooks = $em->createQueryBuilder()
            ->select('COUNT(b.id)')
            ->from(\App\Entity\Book::class, 'b')
            ->where('b.author = :author')
            ->setParameter('author', $author)
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = ceil($totalBooks / $limit);

        return $this->render('admin/authors/books.html.twig', [
            'author' => $author,
            'books' => $books,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

}
