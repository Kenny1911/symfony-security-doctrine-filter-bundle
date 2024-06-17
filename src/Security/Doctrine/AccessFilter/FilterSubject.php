<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter;

final class FilterSubject
{
    /** @var class-string */
    private readonly string $className;

    private readonly string $alias;

    /**
     * @param class-string $className
     */
    public function __construct(string $className, string $alias)
    {
        $this->className = $className;
        $this->alias = $alias;
    }

    /**
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }
}
