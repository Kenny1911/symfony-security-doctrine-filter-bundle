<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter;

abstract class BaseFilter implements Filter
{
    public function apply(string $attribute, FilterSubject $subject, mixed $user): array
    {
        if ($this->supports($attribute, $subject, $user)) {
            return $this->doApply($attribute, $subject, $user);
        }

        return [];
    }

    abstract protected function supports(string $attribute, FilterSubject $subject, mixed $user): bool;

    /**
     * @return list<FilterCondition>
     */
    abstract protected function doApply(string $attribute, FilterSubject $subject, mixed $user): array;

    protected function createConditionBuilder(): FilterConditionBuilder
    {
        return new FilterConditionBuilder();
    }
}
