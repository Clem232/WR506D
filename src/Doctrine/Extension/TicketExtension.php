<?php

namespace App\Doctrine\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Extension qui filtre automatiquement les tickets selon le rôle de l'utilisateur.
 * - ADMIN / SUPER_ADMIN : voient tout
 * - CLIENT : ne voit que les tickets qu'il a créés
 * - AGENT  : ne voit que les tickets qui lui sont assignés
 */
final class TicketExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
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
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== Ticket::class) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user instanceof User) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere(sprintf('%s.id IS NULL', $rootAlias));
            return;
        }

        // Les ADMINS et SUPER_ADMINS voient tout
        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // CLIENT : voit uniquement les tickets qu'il a créés
        if ($this->security->isGranted('ROLE_CLIENT')) {
            $queryBuilder
                ->andWhere(sprintf('%s.creator = :current_user', $rootAlias))
                ->setParameter('current_user', $user);
            return;
        }

        // AGENT : voit les tickets qui lui sont assignés
        if ($this->security->isGranted('ROLE_AGENT')) {
            $queryBuilder
                ->andWhere(sprintf('%s.assignee = :current_user', $rootAlias))
                ->setParameter('current_user', $user);
            return;
        }

        // Par sécurité, si aucun rôle ne correspond, on ne renvoie rien
        $queryBuilder->andWhere(sprintf('%s.id IS NULL', $rootAlias));
    }
}
