<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;

final class DoctrineOrmFilterManager implements FilterManager
{
    /** @var iterable<Filter> */
    private iterable $filters;

    /**
     * @param iterable<Filter> $filters
     */
    public function __construct(iterable $filters)
    {
        $this->filters = $filters;
    }

    public function filter(string $attribute, QueryBuilder $qb, FilterSubject $subject, mixed $user): void
    {
        /** @var list<Expr\Join> $joins */
        $joins = [];

        /** @var list<Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string> $conditions */
        $conditions = [];

        /** @var list<Parameter> $parameters */
        $parameters = [];

        // Get joins, conditions and parameters from filters
        foreach ($this->filters as $filter) {
            foreach ($filter->apply($attribute, $subject, $user) as $filterCondition) {
                $joins = array_merge($joins, $filterCondition->joins);
                $conditions[] = $filterCondition->condition;
                $parameters = array_merge($parameters, $filterCondition->parameters);
            }
        }

        // Add joins
        foreach ($joins as $join) {
            match ($join->getJoinType()) {
                Expr\Join::INNER_JOIN => $qb->innerJoin($join->getJoin(), (string) $join->getAlias(), $join->getConditionType(), $join->getCondition(), $join->getIndexBy()),
                Expr\Join::LEFT_JOIN => $qb->leftJoin($join->getJoin(), (string) $join->getAlias(), $join->getConditionType(), $join->getCondition(), $join->getIndexBy()),
            };
        }

        // Add conditions
        $qb->andWhere(
            $qb->expr()->orX(...$conditions)
        );

        // Add parameters
        foreach ($parameters as $parameter) {
            $qb->setParameter($parameter->getName(), $parameter->getValue(), $parameter->getType());
        }
    }
}
