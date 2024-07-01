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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class AccountManagerController extends AbstractController
{
    #[Route('/api/account', name: 'app_account_create', methods: ['POST'])]
    public function createAccountRequest(
        Request $request, 
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['login'], $data['password'])) {
            throw new UnprocessableEntityHttpException("Paramètres de connection invalides: login ou password manquant(s)");
        }
        
        $allowedParams = ['login', 'password', 'roles', 'status'];
        $unexpectedParams = array_diff(array_keys($data), $allowedParams);
        
        if (!empty($unexpectedParams)) {
            throw new UnprocessableEntityHttpException("Paramètres non autorisés: " . implode(', ', $unexpectedParams));
        }
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException("Il est nécessaire de disposer d'un compte admin pour créer un compte");
        }

        $user = new User();
        $user->setLogin($data['login']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        $user->setUuid(Uuid::uuid4());
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        if (isset($data['roles'])) {
            if (!in_array($data['roles'], [['ROLE_ADMIN'], ['ROLE_USER']], true)) {
                throw new UnprocessableEntityHttpException("Paramètres de connection invalides: admin token manquant et / ou incorrect");
            }
            $user->setStatus($data['status']);
        }

        if (isset($data['status'])) {
            if (!in_array($data['status'], ['open', 'closed'], true)) {
                throw new UnprocessableEntityHttpException("Paramètres de connection invalides: admin token manquant et / ou incorrect");
            }
            $user->setStatus($data['status']);
        }

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            throw new UnprocessableEntityHttpException("Paramètres de connection invalides: admin token manquant et / ou incorrect");
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'uid' => $user->getUuid(),
            'login' => $user->getLogin(),
            'roles' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/account/{uuid}', name: 'app_account_info', methods: ['GET'])]
    public function readAccountRequest(
        string $uuid,
        EntityManagerInterface $em,
        Security $security
    ): JsonResponse {
        if (!Uuid::isValid($uuid)) {
            return new JsonResponse([
                'error' => 'Invalid UUID format.'
            ], Response::HTTP_BAD_REQUEST);
        }
        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            return new JsonResponse(['error' => "Aucun utilisateur trouvé avec l'IUID donné"], JsonResponse::HTTP_NOT_FOUND);
        }

        $currentUser = $security->getUser();
        if ($currentUser->getUserIdentifier() !== $user->getLogin() && !$this->isGranted('ROLE_ADMIN')) {
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
    public function EditAccountRequest(
        string $uuid,
        Request $request,
        EntityManagerInterface $em,
        Security $security,
        ValidatorInterface $validator
    ): JsonResponse {
        if (!Uuid::isValid($uuid)) {
            return new JsonResponse([
                'error' => 'Invalid UUID format.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            throw new JsonResponse(['error' => "Aucun utilisateur trouvé avec l'UUID donné"], JsonResponse::HTTP_NOT_FOUND);
        }

        $currentUser = $security->getUser();
        if ($currentUser->getUserIdentifier() !== $user->getLogin() && !$this->isGranted('ROLE_ADMIN')) {
            throw new JsonResponse(['error' => "Il est nécessaire d'être admin ou d'être le propriétaire du compte"], JsonResponse::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        $requiredParams = ['login', 'password', 'roles', 'status'];
        foreach ($requiredParams as $param) {
            if (!isset($data[$param])) {
                throw new UnprocessableEntityHttpException("Paramètres de connection invalides: admin token manquant et / ou incorrect");
            }
        }
    
        $allowedParams = $requiredParams;
        $unexpectedParams = array_diff(array_keys($data), $allowedParams);
    
        if (!empty($unexpectedParams)) {
            throw new UnprocessableEntityHttpException("Paramètres de connection invalides: admin token manquant et / ou incorrect");
        }

        $validRoles = [['ROLE_ADMIN'], ['ROLE_USER']];
        if (!in_array($data['roles'], $validRoles)) {
            throw new UnprocessableEntityHttpException("Paramètres de connection invalides: admin token manquant et / ou incorrect");
        }
    
        $validStatuses = ['open', 'closed'];
        if (!in_array($data['status'], $validStatuses)) {
            throw new UnprocessableEntityHttpException("Paramètres de connection invalides: admin token manquant et / ou incorrect");
        }

        $user->setLogin($data['login']);
        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
        
        if (!$this->isGranted('ROLE_ADMIN') && $data['roles'] === ["ROLE_ADMIN"]) {
            throw new AccessDeniedHttpException("Il est nécessaire de disposer d'un compte admin pour créer un compte");
        }   

        $user->setRoles($data['roles']);
        $user->setStatus($data['status']);
        $user->setUpdatedAt(new \DateTimeImmutable());

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
            'status' => $user->getStatus(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return new JsonResponse($userData);
    }
}   
