<?php
namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/books')]
class ApiBookController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    private function checkHeader(Request $request): ?JsonResponse
    {
        if ($request->headers->get('X-API-User-Name') !== 'admin') {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }
        return null;
    }

    #[Route('/list', name: 'api_books_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        if ($res = $this->checkHeader($request)) return $res;

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(50, (int) $request->query->get('limit', 10)));
        $offset = ($page - 1) * $limit;

        $repo = $this->em->getRepository(Book::class);

        $total = $repo->count([]);

        $books = $repo->findBy([], ['id' => 'DESC'], $limit, $offset);

        $items = array_map(fn(Book $b) => [
            'id' => $b->getId(),
            'title' => $b->getTitle(),
            'author' => $b->getAuthor()->getName(),
        ], $books);

        return new JsonResponse([
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
            'items' => $items,
        ]);
    }

    #[Route('/by-id', name: 'api_books_by_id', methods: ['GET'])]
    public function byId(Request $request): JsonResponse
    {
        if ($res = $this->checkHeader($request)) return $res;

        $id = $request->query->get('id');
        $book = $this->em->getRepository(Book::class)->find($id);
        if (!$book) return new JsonResponse(['error' => 'Not found'], 404);

        return new JsonResponse([
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => $book->getAuthor()->getName(),
        ]);
    }

    #[Route('/update', name: 'api_books_update', methods: ['POST'])]
    public function update(Request $request): JsonResponse
    {
        if ($res = $this->checkHeader($request)) {
            return $res;
        }

        $data = json_decode($request->getContent(), true);

        $id = $data['id'] ?? $request->get('id');
        $title = $data['title'] ?? $request->get('title');

        if (!$id) {
            return new JsonResponse(['error' => 'Missing "id" parameter'], 400);
        }

        if (!$title) {
            return new JsonResponse(['error' => 'Missing "title" parameter'], 400);
        }

        $book = $this->em->getRepository(Book::class)->find($id);
        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], 404);
        }

        $book->setTitle($title);
        $this->em->flush();

        return new JsonResponse(['status' => 'ok']);
    }


    #[Route('/delete/{id}', name: 'api_books_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $book = $this->em->getRepository(Book::class)->find($id);
        if (!$book) return new JsonResponse(['error' => 'Not found'], 404);

        $this->em->remove($book);
        $this->em->flush();

        return new JsonResponse(['status' => 'deleted']);
    }
}
