<?php declare(strict_types=1);

namespace App\Validator;

use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validateur pour la contrainte MaxOpenTickets.
 * Vérifie que le créateur du ticket n'a pas déjà 10 tickets ouverts.
 */
class MaxOpenTicketsValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TicketRepository $ticketRepository,
        private readonly Security $security,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MaxOpenTickets) {
            throw new UnexpectedTypeException($constraint, MaxOpenTickets::class);
        }

        if (!$value instanceof Ticket) {
            return;
        }

        // Ne vérifier que pour les nouveaux tickets (pas de UUID = pas encore persisté)
        if ($value->getId() !== null) {
            return;
        }

        $user = $value->getCreator() ?? $this->security->getUser();

        if ($user === null) {
            return;
        }

        $openTicketsCount = $this->ticketRepository->countOpenTicketsByUser($user);

        if ($openTicketsCount >= $constraint->limit) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ limit }}', (string) $constraint->limit)
                ->addViolation();
        }
    }
}
