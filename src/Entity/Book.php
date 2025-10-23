<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "books")]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\ManyToOne(targetEntity: Author::class, inversedBy: "books")]
    #[ORM\JoinColumn(nullable: false)]
    private Author $author;

    public function __construct(string $title = '', ?Author $author = null)
    {
        $this->title = $title;
        if ($author) {
            $this->author = $author;
        }
    }

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getAuthor(): Author { return $this->author; }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setAuthor(Author $author): self
    {
        $this->author = $author;
        return $this;
    }
}
