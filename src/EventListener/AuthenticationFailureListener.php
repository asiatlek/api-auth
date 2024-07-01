<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\AuthenticationFailureEvent;

class AuthenticationFailureListener
{
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $exception = $event->getAuthenticationException();

        $data = [
            'code' => 401,
            'message' => 'Échec de l\'authentification : ' . $exception->getMessageKey(),
        ];

        $response = new JsonResponse($data, 401);

        $event->setResponse($response);
    }
}
