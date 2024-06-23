<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter;

use Doctrine\ORM\QueryBuilder;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\Exception\InvalidQueryBuilderTypeException;

interface FilterManager
{
    /**
     * @throws InvalidQueryBuilderTypeException
     */
    public function filter(string $attribute, QueryBuilder $qb, FilterSubject $subject, mixed $user): void;
}
