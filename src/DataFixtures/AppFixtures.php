<?php

namespace App\DataFixtures;

use App\Constants\UserConstants;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct
    (
        private readonly UserPasswordHasherInterface $userPasswordHasherInterface
    )
    {
    }

    private function makeUser(string $email, string $password, array $roles, string $username, string $firstname, string $lastname, string $biography): User
    {
        return (new User())
            ->setEmail($email)
            ->setPassword($this->userPasswordHasherInterface->hashPassword(new User(), $password))
            ->setRoles($roles)
            ->setUsername($username)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setBiography($biography);
    }

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        // Make 1 admin
        $manager->persist($this->makeUser(
            "admin@esgisign.fr",
            "admin",
            [UserConstants::ROLE_ADMIN],
            "admin",
            "Admin",
            "ADMIN",
            "Bio"
        ));

        // Make 20 users
        for ($i = 0; $i < 20; $i++) {
            $manager->persist($this->makeUser(
                "user" . $i . "@esgisign.fr",
                "user" . $i,
                [],
                "user" . $i,
                "User" . $i,
                "USER" . $i,
                "Bio"
            ));
        }

        $manager->flush();
    }
}
