<?php

namespace App\Repository;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        parent::__construct($dm, $dm->getUnitOfWork(), $dm->getClassMetadata(User::class));
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder()
            ->field('email')->equals($email)
            ->getQuery()
            ->getSingleResult();
    }

    public function loadUserByIdentifier(string $identifier): ?User
    {
        return $this->findOneByEmail($identifier);
    }

    public function upgradePassword(UserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->getDocumentManager()->flush();
    }

    public function findPaginated(int $page = 1, int $limit = 10): array
    {
        return $this->createQueryBuilder()
            ->skip(($page - 1) * $limit)
            ->limit($limit)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function emailExists(string $email, ?string $excludeUserId = null): bool
    {
        $qb = $this->createQueryBuilder()
            ->field('email')->equals($email);

        if ($excludeUserId) {
            $qb->field('id')->notEqual($excludeUserId);
        }

        return $qb->getQuery()->count() > 0;
    }
}