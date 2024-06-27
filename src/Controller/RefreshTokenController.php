<?php

namespace App\Controller;

use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class RefreshTokenController extends AbstractController
{
    private $refreshTokenManager;
    private $jwtManager;
    private $userProvider;
    private $jwtEncoder;

    public function __construct(
        RefreshTokenManagerInterface $refreshTokenManager, 
        JWTTokenManagerInterface $jwtManager, 
        UserProviderInterface $userProvider,
        JWTEncoderInterface $jwtEncoder
    ) {
        $this->refreshTokenManager = $refreshTokenManager;
        $this->jwtManager = $jwtManager;
        $this->userProvider = $userProvider;
        $this->jwtEncoder = $jwtEncoder;
    }

    /**
     * @Route("/api/token/refresh/{refreshToken}", name="api_token_refresh", methods={"GET"})
     */
    public function refresh(string $refreshToken): JsonResponse
    {
        $refreshTokenEntity = $this->refreshTokenManager->get($refreshToken);

        if (!$refreshTokenEntity) {
            return new JsonResponse(['error' => 'Invalid refresh token'], 401);
        }

        try {
            $user = $this->userProvider->loadUserByIdentifier($refreshTokenEntity->getUsername());
        } catch (UserNotFoundException $e) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $newJwtToken = $this->jwtManager->create($user);

        // Get payload of the new JWT token
        $payloadAccessToken = $this->jwtEncoder->decode($newJwtToken);
        $accessTokenExpiresAt = (new \DateTime())->setTimestamp($payloadAccessToken['exp']);
        
        // Get expiration of the refresh token
        $refreshTokenExpiresAt = (new \DateTime())->setTimestamp($refreshTokenEntity->getValid()->getTimestamp());

        // Prepare response data
        $data = [
            'token' => $newJwtToken,
            'refreshToken' => $refreshToken,
            'accessTokenExpiresAt' => $accessTokenExpiresAt->format('Y-m-d H:i:s'),
            'refreshTokenExpiresAt' => $refreshTokenExpiresAt->format('Y-m-d H:i:s')
        ];

        return new JsonResponse($data);
    }
}
