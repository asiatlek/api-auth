<?php 

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Création d'un utilisateur administrateur
        $admin = new User();
        $admin->setUuid(Uuid::uuid4()); // Génération d'un UUID aléatoire
        $admin->setLogin('admin');
        $admin->setRoles(['ROLE_ADMIN']); // Rôle administrateur
        $admin->setPassword(password_hash('admin', PASSWORD_DEFAULT)); // Utilisez un bon hachage de mot de passe en production
        $admin->setCreatedAt(new \DateTimeImmutable());
        $admin->setStatus('open');

        // Persistez l'utilisateur en base de données
        $manager->persist($admin);

        // Flush pour enregistrer les modifications
        $manager->flush();
    }
}
