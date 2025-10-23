<?php
namespace App\Command;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Connection;

class GenerateBooksCommand extends Command
{
    protected static $defaultName = 'app:generate-books';
    private $em;
    private $conn;
    private $batchSize = 1000;

    public function __construct(EntityManagerInterface $em, Connection $conn)
    {
        $this->em = $em;
        $this->conn = $conn;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:generate-books')
            ->setDescription('Generate authors and books for testing.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $authorsData = ['Author One', 'Author Two', 'Author Three'];

        $output->writeln("Cleaning old data...");
        $this->conn->executeStatement('DELETE FROM books');
        $this->conn->executeStatement('DELETE FROM authors');
        $output->writeln("Old data removed.");

        foreach ($authorsData as $authorName) {
            // Avoid duplicate author
            $existingAuthor = $this->em->getRepository(Author::class)->findOneBy(['name' => $authorName]);
            if (!$existingAuthor) {
                $author = new Author($authorName);
                $this->em->persist($author);
                $this->em->flush();
            } else {
                $author = $existingAuthor;
            }

            $authorId = $author->getId();

            $output->writeln("Generating books for $authorName...");

            $booksData = [];
            for ($num = 1; $num <= 100000; $num++) {
                $booksData[] = [
                    'title' => "Book $num by " . $author->getName(),
                    'author_id' => $authorId
                ];

                if (count($booksData) >= $this->batchSize) {
                    $this->insertBooks($booksData);
                    $booksData = [];
                    $output->writeln("Inserted $num books for $authorName...");
                }
            }

            if (count($booksData) > 0) {
                $this->insertBooks($booksData);
            }

            $output->writeln("Finished author: $authorName");
        }

        $output->writeln('Books generated successfully!');
        return Command::SUCCESS;
    }

    private function insertBooks(array $booksData): void
    {
        $values = [];
        $params = [];
        $i = 0;

        foreach ($booksData as $book) {
            $values[] = "(:title$i, :author_id$i)";
            $params["title$i"] = $book['title'];
            $params["author_id$i"] = $book['author_id'];
            $i++;
        }

        $sql = 'INSERT INTO books (title, author_id) VALUES ' . implode(', ', $values);
        $this->conn->executeStatement($sql, $params);
    }

}
