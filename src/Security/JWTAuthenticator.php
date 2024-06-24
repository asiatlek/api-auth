<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator as BaseJWTAuthenticator;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTAuthenticator extends BaseJWTAuthenticator
{
    protected function getUser($preAuthToken, UserProviderInterface $userProvider): ?UserInterface
    {
        $data = $preAuthToken->getPayload();
        $uuid = $data['uuid'] ?? null;

        if (null === $uuid) {
            throw new AuthenticationException('Invalid token');
        }

        return $userProvider->loadUserByIdentifier($uuid);
    }
}