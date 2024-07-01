<?php
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTFailureListener
{
    private const ERROR_MESSAGE = "Il est nécessaire d'être authentifié";

    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $this->setJsonResponse($event, self::ERROR_MESSAGE);
    }

    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $this->setJsonResponse($event, self::ERROR_MESSAGE);
    }

    public function onJWTExpired(JWTExpiredEvent $event)
    {
        $this->setJsonResponse($event, self::ERROR_MESSAGE);
    }

    private function setJsonResponse($event, string $message)
    {
        $data = [
            'code' => 401,
            'description' => $message,
        ];

        $response = new JsonResponse($data, 401);
        $event->setResponse($response);
    }
}
