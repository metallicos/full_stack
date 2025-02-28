<?php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ArticleRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 100)]
    public string $title;

    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    public string $content;
}