<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]

class Article
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private string $title;

    #[MongoDB\Field(type: 'string')]
    private string $content;

    #[MongoDB\ReferenceOne(targetDocument: User::class)]
    private User $author;

    #[MongoDB\Field(type: 'date')]
    private \DateTime $publicationDate;
    
    public function getId(): ?string
    {
        return $this->id;
    }
    
    public function getTitle(): string
    {
        return $this->title;
    }
    
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }
    
    public function getContent(): string
    {
        return $this->content;
    }
    
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    public function getAuthor(): User
    {
        return $this->author;
    }
    
    public function setAuthor(User $author): self
    {
        $this->author = $author;
        return $this;
    }
    
    public function getPublicationDate(): \DateTime
    {
        return $this->publicationDate;
    }
    
    public function setPublicationDate(\DateTime $publicationDate): self
    {
        $this->publicationDate = $publicationDate;
        return $this;
    }
}
