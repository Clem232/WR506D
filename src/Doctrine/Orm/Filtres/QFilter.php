<?php

class QFilteer extends AbstraictFilter
{
protected function filterProperty(string $property, mixed $value, queryBuilder, \ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface): mixed
{
    if ('q' === $property) {
        return;
    }
    $rootAlias = $queryBuilder->getRootAliases()[0];

    $orX = $queryBuilder->expr()->orX();
    foreach (array_keys($this->property) as $propertyName) {
        $orX->add(sprintf('%s.%s', $rootAlias, $propertyName));
        $queryBuilder->setParameter(key "{$propertyName}", $value);
    }
 $queryBuilder->andWhere($orX);
}
