<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Register;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegisterProcessor implements ProcessorInterface
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        assert($data instanceof Register);

        $user = new User();
        $user->setEmail($data->email);
        $user->setName($data->name ?? $data->email); // Utilise le nom s'il est fourni, sinon l'email
        $user->setRole('ROLE_CLIENT');

        // Hachage du mot de passe
        $hashedPassword = $this->hasher->hashPassword($user, $data->password);
        $user->setPassword($hashedPassword);

        // Sauvegarde
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
