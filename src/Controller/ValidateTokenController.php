<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ValidateTokenController extends AbstractController
{
    #[Route('/api/validate/{token}', name: 'app_validate_token', methods: ['GET'])]
    public function validateToken(
        string $token,
        JWTEncoderInterface $jwtEncoder
    ): JsonResponse {
        try {
            $payload = $jwtEncoder->decode($token);
        } catch (AuthenticationException $e) {
            return new JsonResponse(['error' => 'Token non trouvé / invalide'], JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token non trouvé / invalide'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Convertir l'horodatage Unix en objet DateTime
        if (isset($payload['exp'])) {
            $expiresAt = (new \DateTime())->setTimestamp($payload['exp']);
        } else {
            return new JsonResponse(['error' => 'Token non trouvé / invalide'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'accessToken' => $token,
            'accessTokenExpiresAt' => $expiresAt->format('Y-m-d H:i:s')
        ]);
    }
}
