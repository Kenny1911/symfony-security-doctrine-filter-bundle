<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;
use LogicException;

final class FilterConditionBuilder
{
    private Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string|null $condition = null;

    /** @var list<Expr\Join> */
    private array $joins = [];

    /** @var list<Parameter> */
    private array $parameters = [];

    public function setCondition(Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @psalm-param Expr\Join::ON|Expr\Join::WITH|null $conditionType
     *
     * @see QueryBuilder::join()
     */
    public function join(
        string $join,
        string $alias,
        ?string $conditionType = null,
        string|Expr\Composite|Expr\Comparison|Expr\Func|null $condition = null,
        ?string $indexBy = null,
    ): self {
        return $this->innerJoin($join, $alias, $conditionType, $condition, $indexBy);
    }

    /**
     * @psalm-param Expr\Join::ON|Expr\Join::WITH|null $conditionType
     *
     * @see QueryBuilder::innerJoin()
     */
    public function innerJoin(
        string $join,
        string $alias,
        ?string $conditionType = null,
        string|Expr\Composite|Expr\Comparison|Expr\Func|null $condition = null,
        ?string $indexBy = null,
    ): self {
        $this->joins[] = new Expr\Join(
            Expr\Join::INNER_JOIN,
            $join,
            $alias,
            $conditionType,
            $condition,
            $indexBy,
        );

        return $this;
    }

    /**
     * @psalm-param Expr\Join::ON|Expr\Join::WITH|null $conditionType
     *
     * @see QueryBuilder::leftJoin()
     */
    public function leftJoin(
        string $join,
        string $alias,
        ?string $conditionType = null,
        string|Expr\Composite|Expr\Comparison|Expr\Func|null $condition = null,
        ?string $indexBy = null,
    ): self {
        $this->joins[] = new Expr\Join(
            Expr\Join::LEFT_JOIN,
            $join,
            $alias,
            $conditionType,
            $condition,
            $indexBy,
        );

        return $this;
    }

    /**
     * @see QueryBuilder::setParameter()
     */
    public function setParameter(string|int $key, mixed $value, ParameterType|ArrayParameterType|string|int|null $type = null): self
    {
        foreach ($this->parameters as $parameter) {
            if (Parameter::normalizeName($key) === $parameter->getName()) {
                $parameter->setValue($value, $type);

                return $this;
            }
        }

        $this->parameters[] = new Parameter($key, $value, $type);

        return $this;
    }

    public function build(): FilterCondition
    {
        return new FilterCondition(
            $this->condition ?? throw new LogicException('Condition not set.'),
            $this->joins,
            $this->parameters,
        );
    }
}
