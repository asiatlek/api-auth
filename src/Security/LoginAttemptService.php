<?php

namespace App\Security;

use App\Repository\LoginAttemptRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\LoginAttempt;
use DateTime;

class LoginAttemptService
{
    private LoginAttemptRepository $loginAttemptRepository;
    private EntityManagerInterface $em;

    public function __construct(LoginAttemptRepository $loginAttemptRepository, EntityManagerInterface $em)
    {
        $this->loginAttemptRepository = $loginAttemptRepository;
        $this->em = $em;
    }

    public function logFailedAttempt(string $ip): void
    {
        $attempt = new LoginAttempt();
        $attempt->setIp($ip);
        $attempt->setAttemptedAt(new DateTime());

        $this->em->persist($attempt);
        $this->em->flush();
    }

    public function isBlocked(string $ip): bool
    {
        $attempts = $this->loginAttemptRepository->countRecentAttempts($ip);

        return $attempts > 3;
    }
}
