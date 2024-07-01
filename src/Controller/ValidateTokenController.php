<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ValidateTokenController extends AbstractController
{
    #[Route('/api/validate/{token}', name: 'api_validate_token', methods: ['GET'], schemes: "https")]
    public function validateToken(
        string $token,
        JWTEncoderInterface $jwtEncoder
    ): JsonResponse {
        // Vérifier la présence du token
        if (empty($token)) {
            throw new NotFoundHttpException('Token non trouvé / invalide');        
        }

        // Vérifier le format du token
        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            throw new NotFoundHttpException('Token non trouvé / invalide');        
        }

        try {
            $payload = $jwtEncoder->decode($token);
        } catch (\Exception $e) {
            throw new NotFoundHttpException('Token non trouvé / invalide');        
        }

        if (isset($payload['exp'])) {
            $expiresAt = (new \DateTime())->setTimestamp($payload['exp']);
        } else {
            throw new NotFoundHttpException('Token non trouvé / invalide');        
        }

        return new JsonResponse([
            'accessToken' => $token,
            'accessTokenExpiresAt' => $expiresAt->format('Y-m-d H:i:s')
        ]);
    }
}
