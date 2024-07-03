<?php

// src/Security/JWTAuthenticationFailureHandler.php

namespace App\Security;

use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use App\Security\LoginAttemptService;

class JWTAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    private LoginAttemptService $loginAttemptService;

    public function __construct(LoginAttemptService $loginAttemptService)
    {
        $this->loginAttemptService = $loginAttemptService;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $ip = $request->getClientIp();
        $this->loginAttemptService->logFailedAttempt($ip);

        if ($this->loginAttemptService->isBlocked($ip)) {
            return new JsonResponse(['message' => 'Too many login attempts, please try again later.'], 429);
        }

        return new JsonResponse(['message' => 'Invalid credentials.'], 401);
    }
}
