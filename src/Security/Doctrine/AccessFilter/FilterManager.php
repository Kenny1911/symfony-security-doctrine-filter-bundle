<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter;

use Doctrine\ORM\QueryBuilder;

interface FilterManager
{
    public function filter(string $attribute, QueryBuilder $qb, FilterSubject $subject, mixed $user): void;
}
