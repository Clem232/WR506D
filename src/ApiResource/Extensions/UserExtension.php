<?php

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Extension custom qui filtre automatiquement les tickets selon le rôle de l'utilisateur
 * - CLIENT : voit uniquement ses tickets
 * - AGENT : voit les tickets qui lui sont assignés
 * - ADMIN : voit tout
 */
final class TicketExtension implements QueryCollectionExtensionInterface
{
    public function __construct(
        private Security $security
    ) {}

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        // On applique cette extension uniquement pour l'entité Ticket
        if ($resourceClass !== Ticket::class) {
            return;
        }

        $user = $this->security->getUser();

        // Si pas d'utilisateur connecté, on ne montre rien
        if (!$user instanceof User) {
            $queryBuilder
                ->andWhere('1 = 0'); // Requête qui ne retourne rien
            return;
        }

        // Les admins voient tout
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Les clients voient uniquement leurs tickets
        if (in_array('ROLE_CLIENT', $user->getRoles())) {
            $queryBuilder
                ->andWhere(sprintf('%s.client = :current_user', $rootAlias))
                ->setParameter('current_user', $user);
            return;
        }

        // Les agents voient les tickets qui leur sont assignés
        if (in_array('ROLE_AGENT', $user->getRoles())) {
            $queryBuilder
                ->andWhere(sprintf('%s.assignedAgent = :current_user', $rootAlias))
                ->setParameter('current_user', $user);
            return;
        }

        // Par défaut, on ne montre rien
        $queryBuilder->andWhere('1 = 0');
    }
}
