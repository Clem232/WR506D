<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Ticket;

/**
 * Événement déclenché quand le statut d'un ticket change.
 */
final class TicketStatusChangedEvent
{
    public function __construct(
        public readonly Ticket $ticket,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {
    }
}
