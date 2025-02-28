<?php
namespace App\Controller;

use App\Document\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api/users', name: 'users_')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {}

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function getMe(#[CurrentUser] User $user): JsonResponse
    {
        return $this->json($this->formatUser($user));
    }

    #[Route('/me', name: 'update_me', methods: ['PUT'])]
    public function updateMe(
        #[CurrentUser] User $user,
        Request $request
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'email'    => [new Assert\Email()],
            'name'     => [new Assert\Length(['min' => 2, 'max' => 50])],
            'password' => [new Assert\Length(['min' => 6])],
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        if (isset($data['email']) && $data['email'] !== $user->getEmail()) {
            if ($this->userRepository->emailExists($data['email'], $user->getId())) {
                return $this->errorResponse('Email already in use', Response::HTTP_CONFLICT);
            }
            $user->setEmail($data['email']);
        }

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        }

        $this->userRepository->getDocumentManager()->flush();

        return $this->json($this->formatUser($user));
    }

    private function formatUser(User $user): array
    {
        return [
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'name'      => $user->getName(),
            'roles'     => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }

    private function validationErrorResponse($errors): JsonResponse
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()] = $error->getMessage();
        }
        return new JsonResponse([
            'error' => [
                'code'    => Response::HTTP_BAD_REQUEST,
                'message' => 'Validation failed',
                'details' => $messages,
            ],
        ], Response::HTTP_BAD_REQUEST);
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return new JsonResponse([
            'error' => [
                'code'    => $status,
                'message' => $message,
            ],
        ], $status);
    }
}
