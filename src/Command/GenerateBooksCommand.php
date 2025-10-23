<?php
namespace App\Command;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateBooksCommand extends Command
{
    // Можно оставить defaultName для Symfony 5.3+
    protected static $defaultName = 'app:generate-books';
    private $em;
    private $batchSize = 1000;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    // Обязательно для старых версий Symfony
    protected function configure(): void
    {
        $this
            ->setName('app:generate-books')
            ->setDescription('Generate authors and books for testing.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $authorsData = ['Author One', 'Author Two', 'Author Three'];

        foreach ($authorsData as $authorName) {
            $author = new Author($authorName);
            $this->em->persist($author);
            $this->em->flush();
            $this->em->clear();

            $author = $this->em->getRepository(Author::class)->findOneBy(['name'=>$authorName]);

            $i = 0;
            foreach (range(1, 100000) as $num) {
                $book = new Book("Book $num by " . $author->getName(), $author);
                $this->em->persist($book);
                $i++;

                if ($i % $this->batchSize === 0) {
                    $this->em->flush();
                    $this->em->clear();
                    $author = $this->em->getRepository(Author::class)->findOneBy(['name'=>$authorName]);
                    $output->writeln("Inserted $i books for $authorName...");
                }
            }

            // flush remaining
            $this->em->flush();
            $this->em->clear();
            $output->writeln("Finished author: $authorName");
        }

        $output->writeln('Books generated successfully!');
        return Command::SUCCESS;
    }
}
