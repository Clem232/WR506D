<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Ticket;
use App\Entity\User;
use App\Event\TicketStatusChangedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TicketProcessor implements ProcessorInterface
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Ticket
    {
        assert($data instanceof Ticket);

        $user = $this->security->getUser();

        // Détecter un changement de statut sur un ticket existant
        $oldStatus = null;
        if ($data->getId() !== null) {
            $original = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);
            $oldStatus = $original['status'] ?? null;
        }

        // Assigner le créateur si c'est un nouveau ticket
        if ($user instanceof User && null === $data->getCreator()) {
            $data->setCreator($user);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        // Dispatcher l'événement si le statut a changé
        if ($oldStatus !== null && $oldStatus !== $data->getStatus()) {
            $this->eventDispatcher->dispatch(
                new TicketStatusChangedEvent($data, $oldStatus, $data->getStatus())
            );
        }

        return $data;
    }
}

