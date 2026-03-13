<?php declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

/**
 * Filtre custom "onlyWithTodo" pour les catégories.
 * Quand onlyWithTodo=true, ne retourne que les catégories ayant des tickets "OPEN" ou "IN_PROGRESS".
 * Quand onlyWithTodo=false (ou absent), retourne toutes les catégories.
 */
final class OnlyWithTodoFilter extends AbstractFilter
{
    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($property !== 'onlyWithTodo') {
            return;
        }

        // Convertir la valeur en boolean
        $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($boolValue !== true) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $ticketAlias = $queryNameGenerator->generateParameterName('tickets');

        $queryBuilder
            ->innerJoin(sprintf('%s.tickets', $alias), $ticketAlias)
            ->andWhere(sprintf('%s.status IN (:open_statuses)', $ticketAlias))
            ->setParameter('open_statuses', ['OPEN', 'IN_PROGRESS'])
            ->distinct();
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'onlyWithTodo' => [
                'property' => 'onlyWithTodo',
                'type' => 'bool',
                'required' => false,
                'description' => 'Filtrer uniquement les catégories ayant des tickets ouverts ou en cours (true/false)',
                'openapi' => [
                    'description' => 'Filtrer les catégories avec des tickets ouverts ou en cours',
                    'name' => 'onlyWithTodo',
                    'type' => 'boolean',
                ],
            ],
        ];
    }
}
