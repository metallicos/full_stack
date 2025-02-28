<?php

namespace App\Controller;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, DocumentManager $dm, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['name'], $data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Missing parameters'], 400);
        }
        
        // Check if the email is already registered.
        $existingUser = $dm->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Email already registered'], 400);
        }

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $dm->persist($user);
        $dm->flush();

        return new JsonResponse(['message' => 'User registered successfully'], 201);
    }
}
