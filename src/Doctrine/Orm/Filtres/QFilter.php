<?php

namespace App\Doctrine\Orm\Filtres;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

class QFilter extends AbstractFilter
{
    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,                // <--- Ajouté
        ?Operation $operation = null,         // <--- Ajouté
        array $context = []                   // <--- Ajouté
    ): void {

        // EXEMPLE DE LOGIQUE DE FILTRE (à adapter selon ton besoin)
        // Ici, on vérifie si la propriété demandée est celle qu'on veut filtrer
        if ($property !== 'q') {
            return;
        }

        // On crée un alias pour la requête
        $alias = $queryBuilder->getRootAliases()[0];

        // Exemple : recherche générique sur le titre ou la description
        // Adapte les champs 'title' et 'description' selon ton entité Ticket
        $queryBuilder
            ->andWhere(sprintf('%s.title LIKE :search OR %s.description LIKE :search', $alias, $alias))
            ->setParameter('search', '%' . $value . '%');
    }

    // Cette méthode est OBLIGATOIRE pour que le filtre apparaisse dans la doc API (Swagger)
    public function getDescription(string $resourceClass): array
    {
        return [
            'q' => [
                'property' => 'q',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Recherche globale (titre ou description)',
                    'name' => 'q',
                    'type' => 'string',
                ],
            ],
        ];
    }
}
