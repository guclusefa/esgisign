<?php
// src/EventListener/UserBanListener.php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticationListener
{
    private TokenStorageInterface $tokenStorage;

        public function __construct(TokenStorageInterface $tokenStorage)
        {
            $this->tokenStorage = $tokenStorage;
        }

        public function onKernelRequest(RequestEvent $event): void
        {
            $token = $this->tokenStorage->getToken();

            if ($token && is_object($user = $token->getUser())) {
                if ($user->isBanned()) {
                    throw new AccessDeniedHttpException('Vous avez été banni');
                }
            }
        }
}
