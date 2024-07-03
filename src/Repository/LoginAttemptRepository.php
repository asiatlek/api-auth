<?php

namespace App\Repository;

use App\Entity\LoginAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTime;

class LoginAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginAttempt::class);
    }

    public function countRecentAttempts(string $ip, int $minutes = 5): int
    {
        $since = new DateTime("-$minutes minutes");

        return $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.ip = :ip')
            ->andWhere('a.attemptedAt > :since')
            ->setParameter('ip', $ip)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
