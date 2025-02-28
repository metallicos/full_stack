<?php
namespace App\Repository;

use App\Document\User;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class ArticleRepository extends DocumentRepository
{
    public function findUserArticles(User $user): array
    {
        return $this->createQueryBuilder()
            ->field('author')->references($user)
            ->getQuery()
            ->execute();
    }
}