<?php

namespace App\Controller;

use App\Document\Article;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/articles')]
class ArticleController extends AbstractController
{
    #[Route('', name: 'list_articles', methods: ['GET'])]
    public function list(DocumentManager $dm): JsonResponse
    {
        $articles = $dm->getRepository(Article::class)->findAll();
        $data = [];
        foreach ($articles as $article) {
            $data[] = [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'content' => $article->getContent(),
                'author' => $article->getAuthor()->getEmail(), // Or use getName()
                'publicationDate' => $article->getPublicationDate()->format('Y-m-d H:i:s'),
            ];
        }
        return new JsonResponse($data);
    }
    
    #[Route('', name: 'create_article', methods: ['POST'])]
    public function create(Request $request, DocumentManager $dm): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }
        
        $data = json_decode($request->getContent(), true);
        if (!isset($data['title'], $data['content'])) {
            return new JsonResponse(['error' => 'Missing parameters'], 400);
        }
        
        $article = new Article();
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setAuthor($user);
        $article->setPublicationDate(new \DateTime());
        
        $dm->persist($article);
        $dm->flush();
        
        return new JsonResponse(['message' => 'Article created successfully'], 201);
    }
    
    #[Route('/{id}', name: 'update_article', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, DocumentManager $dm, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }
        
        $article = $dm->getRepository(Article::class)->find($id);
        if (!$article) {
            return new JsonResponse(['error' => 'Article not found'], 404);
        }
        
        if ($article->getAuthor()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }
        
        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) {
            $article->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $article->setContent($data['content']);
        }
        
        $dm->flush();
        
        return new JsonResponse(['message' => 'Article updated successfully']);
    }
    
    #[Route('/{id}', name: 'delete_article', methods: ['DELETE'])]
    public function delete(DocumentManager $dm, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }
        
        $article = $dm->getRepository(Article::class)->find($id);
        if (!$article) {
            return new JsonResponse(['error' => 'Article not found'], 404);
        }
        
        if ($article->getAuthor()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }
        
        $dm->remove($article);
        $dm->flush();
        
        return new JsonResponse(['message' => 'Article deleted successfully']);
    }
}
