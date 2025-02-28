<?php

namespace App\Security;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class MongoUserProvider implements UserProviderInterface
{
    public function __construct(private DocumentManager $dm) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->dm->getRepository(User::class)->findOneBy(['email' => $identifier]);
        if (!$user) {
            throw new UnsupportedUserException(sprintf('User with email "%s" not found.', $identifier));
        }
        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s".', get_class($user)));
        }
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
