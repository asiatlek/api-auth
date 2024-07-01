<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JsonExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        // print_r($exception);die;
        
        $response = $this->createResponseFromException($exception);
        
        $event->setResponse($response);
    }

    private function createResponseFromException(\Throwable $exception): JsonResponse
    {
        if ($exception instanceof BadRequestHttpException && $exception->getMessage() === 'Invalid JSON.') {
            return new JsonResponse([
                'code' => 422,
                'message' => 'Paramètres de connection invalides: login ou password manquant(s)',
            ], 422);
        }

        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
        $message = $exception instanceof HttpExceptionInterface ? $exception->getMessage() : 'Erreur interne du serveur';

        return new JsonResponse([
            'code' => $statusCode,
            'description' => $message,
        ], $statusCode);
    }
}
