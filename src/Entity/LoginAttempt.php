<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeInterface;
use App\Repository\LoginAttemptRepository;

#[ORM\Entity(repositoryClass: LoginAttemptRepository::class)]
class LoginAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 45)]
    private string $ip;

    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $attemptedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;
        return $this;
    }

    public function getAttemptedAt(): DateTimeInterface
    {
        return $this->attemptedAt;
    }

    public function setAttemptedAt(DateTimeInterface $attemptedAt): self
    {
        $this->attemptedAt = $attemptedAt;
        return $this;
    }
}
