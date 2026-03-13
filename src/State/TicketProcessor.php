<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class TicketProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Ticket
    {
        assert($data instanceof Ticket);

        // On récupère l'utilisateur connecté
        $user = $this->security->getUser();

        // Si c'est un ticket tout neuf et qu'un utilisateur est connecté
        if ($user instanceof User && null === $data->getCreator()) {
            $data->setCreator($user);
        }

        // On sauvegarde
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
