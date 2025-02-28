<?php
namespace App\Controller;

use App\Document\Article;
use App\Document\User;
use App\Dto\ArticleRequest;
use App\Repository\ArticleRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('api/articles')]
#[IsGranted('ROLE_USER')]
class ArticleController extends AbstractController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private DocumentManager $dm
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page  = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $paginator = $this->articleRepository->paginate($page, $limit);

        return $this->json([
            'data' => array_map([$this, 'formatArticle'], $paginator->getIterator()),
            'meta' => [
                'total' => $paginator->count(),
                'page'  => $page,
                'limit' => $limit,
            ],
        ]);
    }

    #[Route('/create', methods: ['POST'])]
    public function create(ArticleRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $article = new Article();
        $article->setTitle($request->title)
            ->setContent($request->content)
            ->setAuthor($user);

        $this->dm->persist($article);
        $this->dm->flush();

        return $this->json(
            $this->formatArticle($article),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(
        #[MapEntity] Article $article,
        Request $request
    ): JsonResponse {
        if ($article->getAuthor()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        $article->setTitle($request->title)
            ->setContent($request->content);

        $this->dm->flush();

        return $this->json($this->formatArticle($article));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Article $article): JsonResponse
    {
        if ($article->getAuthor()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        $this->dm->remove($article);
        $this->dm->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function formatArticle(Article $article): array
    {
        return [
            'id'              => $article->getId(),
            'title'           => $article->getTitle(),
            'content'         => $article->getContent(),
            'author'          => $article->getAuthor()->getUserIdentifier(),
            'publicationDate' => $article->getPublicationDate()->format(\DateTimeInterface::ATOM),
        ];
    }
}
