<?php
// src/Controller/UserController.php
namespace App\Controller;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/account')]
class UserController extends AbstractController
{
    #[Route('', name: 'user_info', methods: ['GET'])]
    public function info(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }
        return new JsonResponse([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ]);
    }
    
    #[Route('', name: 'update_user', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, DocumentManager $dm): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }
        
        $data = json_decode($request->getContent(), true);
        if (isset($data['name'])) {
            $user->setName($data['name']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        
        $dm->flush();
        
        return new JsonResponse(['message' => 'User updated successfully']);
    }
}
