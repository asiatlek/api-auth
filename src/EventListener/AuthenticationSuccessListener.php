<?php

// src/EventListener/AuthenticationSuccessListener.php
namespace App\EventListener;

use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class AuthenticationSuccessListener
{
    private $refreshTokenManager;
    private $requestStack;
    private $jwtEncoder;

    public function __construct(RefreshTokenManagerInterface $refreshTokenManager, RequestStack $requestStack, JWTEncoderInterface $jwtEncoder)
    {
        $this->refreshTokenManager = $refreshTokenManager;
        $this->requestStack = $requestStack;
        $this->jwtEncoder = $jwtEncoder;
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $refreshToken = $this->refreshTokenManager->getLastFromUsername($user);
        
        if (!$refreshToken) {
            $refreshToken = $this->refreshTokenManager->create();
            $refreshToken->setUsername($user->getUserIdentifier());
            $refreshToken->setRefreshToken(bin2hex(random_bytes(64)));
            $refreshToken->setValid((new \DateTime())->modify('+1 month'));
            $this->refreshTokenManager->save($refreshToken);
        }

        $data['accessToken'] = $data['token'];
        unset($data['token']);
        $payloadAccessToken = $this->jwtEncoder->decode($data['accessToken']);
        $accesTokenExpiresAt = (new \DateTime())->setTimestamp($payloadAccessToken['exp']);
        $refreshTokenExpiresAt = (new \DateTime())->setTimestamp($refreshToken->getValid()->getTimestamp());
        $data['accesTokenExpiresAt'] = $accesTokenExpiresAt->format('Y-m-d H:i:s');
        $data['refreshTokenExpiresAt'] = $refreshTokenExpiresAt->format('Y-m-d H:i:s');

        $event->setData($data);
    }
}
