<?php

namespace App\EventListener;

use Gesdinet\JWTRefreshTokenBundle\Event\RefreshEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class RefreshTokenListener
{
    public function onRefreshToken(RefreshEvent $event)
    {
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $newToken = $event->getNewAccessToken();
        $data = $newToken->getData();

        // Vous pouvez personnaliser le payload du nouveau token ici
        // Par exemple, ajouter l'UUID de l'utilisateur
        $data['uuid'] = $user->getLogin();

        // Mettre à jour le payload du nouveau token
        $newToken->setData($data);
    }
}