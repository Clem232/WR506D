<?php declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Contrainte de validation : un client ne peut pas avoir plus de 10 tickets ouverts.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class MaxOpenTickets extends Constraint
{
    public string $message = 'Vous avez déjà {{ limit }} tickets ouverts. Veuillez en fermer avant d\'en créer un nouveau.';
    public int $limit = 10;

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
