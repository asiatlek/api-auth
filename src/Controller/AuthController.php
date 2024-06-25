<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\SecurityBundle\Security;


class AuthController extends AbstractController
{
    #[Route('/api/account', name: 'app_account', methods: ['POST'])]
    public function registerAccount(
        Request $request, 
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['login'], $data['password'])) {
            return new JsonResponse(['error' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => "Il est nécessaire de disposé d'un compte admin pour créer un compte"], JsonResponse::HTTP_FORBIDDEN);
        }

        $user = new User();
        $user->setLogin($data['login']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setUuid(uniqid());
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        if (isset($data['status']) && $data['status'] === "closed") {
            $user->setStatus("closed");
        }

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'uid' => $user->getUserIdentifier(),
            'login' => $user->getLogin(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/account/{uuid}', name: 'app_account_info', methods: ['GET'])]
    public function getAccountInfo(
        string $uuid,
        EntityManagerInterface $em,
        Security $security
    ): JsonResponse {
        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            return new JsonResponse(['error' => "Aucun utilisateur trouvé avec l'IUID donné"], JsonResponse::HTTP_NOT_FOUND);
        }

        // Vérifiez si l'utilisateur authentifié est le propriétaire du compte ou un administrateur
        $currentUser = $security->getUser();
        if ($currentUser->getUserIdentifier() !== $uuid && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => "Il est nécessaire d'être admin ou d'être le proprietaire du compte"], JsonResponse::HTTP_FORBIDDEN);
        }

        $userData = [
            'uid' => $user->getUuid(),
            'login' => $user->getLogin(),
            'roles' => $user->getRoles(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return new JsonResponse($userData);
    }

    #[Route('/api/account/{uuid}', name: 'app_account_update', methods: ['PUT'])]
    public function updateAccount(
        string $uuid,
        Request $request,
        EntityManagerInterface $em,
        Security $security,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            return new JsonResponse(['error' => "Aucun utilisateur trouvé avec l'UUID donné"], JsonResponse::HTTP_NOT_FOUND);
        }

        // Vérifiez si l'utilisateur authentifié est le propriétaire du compte ou un administrateur
        $currentUser = $security->getUser();
        if ($currentUser->getUserIdentifier() !== $uuid && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => "Il est nécessaire d'être admin ou d'être le propriétaire du compte"], JsonResponse::HTTP_FORBIDDEN);
        }

        // Mise à jour des informations de l'utilisateur
        if (isset($data['login'])) {
            $user->setLogin($data['login']);
        }
        if (isset($data['password'])) {
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        }
        if ($this->isGranted('ROLE_ADMIN') && isset($data['roles'])) {
            if ($data['roles'] === "ROLE_ADMIN") {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }
        }

        $user->setUpdatedAt(new \DateTimeImmutable());

        // Validation des données
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($user);
        $em->flush();

        $userData = [
            'uid' => $user->getUuid(),
            'login' => $user->getLogin(),
            'roles' => $user->getRoles(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return new JsonResponse($userData);
    }

    #[Route('/api/refresh-token/{refreshToken}', name: 'app_refresh', methods: ['POST'])]
    public function refresh($refreshToken)
    {
        return $this->json($refreshToken);
    }
}
