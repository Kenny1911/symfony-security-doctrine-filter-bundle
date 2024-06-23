<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter;

use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\Util\UniqueNamer;

abstract class BaseFilter implements Filter
{
    public function apply(string $attribute, ReadableQueryBuilder $qb, FilterSubject $subject, mixed $user): array
    {
        if ($this->supports($attribute, $qb, $subject, $user)) {
            return $this->doApply($attribute, $qb, $subject, $user);
        }

        return [];
    }

    abstract protected function supports(string $attribute, ReadableQueryBuilder $qb, FilterSubject $subject, mixed $user): bool;

    /**
     * @return list<FilterCondition>
     */
    abstract protected function doApply(string $attribute, ReadableQueryBuilder $qb, FilterSubject $subject, mixed $user): array;

    protected function createConditionBuilder(): FilterConditionBuilder
    {
        return new FilterConditionBuilder();
    }

    protected function uniqueParamName(ReadableQueryBuilder $qb, int|string $name): string
    {
        return UniqueNamer::uniqueParamName($qb, $name);
    }

    protected function uniqueAlias(ReadableQueryBuilder $qb, string $alias): string
    {
        return UniqueNamer::uniqueAlias($qb, $alias);
    }
}
