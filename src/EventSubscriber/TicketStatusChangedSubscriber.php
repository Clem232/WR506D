<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\TicketStatusChangedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber qui réagit aux changements de statut d'un ticket.
 * Exemple : log de l'événement (extensible en notifications email, etc.)
 */
final class TicketStatusChangedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TicketStatusChangedEvent::class => 'onStatusChanged',
        ];
    }

    public function onStatusChanged(TicketStatusChangedEvent $event): void
    {
        $this->logger->info(
            sprintf(
                '[Ticket #%s] Statut changé : %s → %s',
                $event->ticket->getId(),
                $event->oldStatus,
                $event->newStatus,
            )
        );
    }
}
