<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter;

use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\ORM\Cache;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;

/**
 * Wrapper of {@see QueryBuilder} only for read.
 */
final class ReadableQueryBuilder
{
    private readonly QueryBuilder $qb;

    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    public function __toString(): string
    {
        return (string) $this->qb;
    }

    public function expr(): Expr
    {
        return $this->qb->expr();
    }

    public function isCacheable(): bool
    {
        return $this->qb->isCacheable();
    }

    public function getCacheRegion(): ?string
    {
        return $this->qb->getCacheRegion();
    }

    public function getLifetime(): int
    {
        return $this->qb->getLifetime();
    }

    /**
     * @psalm-return Cache::MODE_*|null
     */
    public function getCacheMode(): ?int
    {
        return $this->qb->getCacheMode();
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->qb->getEntityManager();
    }

    public function getDQL(): string
    {
        return $this->qb->getDQL();
    }

    /**
     * @return list<string>
     */
    public function getRootAliases(): array
    {
        return $this->qb->getRootAliases();
    }

    /**
     * @return list<string>
     */
    public function getAllAliases(): array
    {
        return $this->qb->getAllAliases();
    }

    /**
     * @return list<class-string>
     */
    public function getRootEntities(): array
    {
        return $this->qb->getRootEntities();
    }

    /**
     * @return ReadableCollection<int, Parameter>
     */
    public function getParameters(): ReadableCollection
    {
        return $this->qb->getParameters();
    }

    public function getParameter(string|int $key): ?Parameter
    {
        return $this->qb->getParameter($key);
    }

    public function getFirstResult(): int
    {
        return $this->qb->getFirstResult();
    }

    public function getMaxResults(): ?int
    {
        return $this->qb->getMaxResults();
    }

    public function getDQLPart(string $queryPartName): mixed
    {
        return $this->qb->getDQLPart($queryPartName);
    }

    /**
     * @return array<string, mixed>
     */
    public function getDQLParts(): array
    {
        return $this->qb->getDQLParts();
    }
}
