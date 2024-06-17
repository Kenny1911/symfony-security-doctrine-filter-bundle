<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter;

interface Filter
{
    /**
     * @return list<FilterCondition>
     */
    public function apply(string $attribute, FilterSubject $subject, mixed $user): array;
}
