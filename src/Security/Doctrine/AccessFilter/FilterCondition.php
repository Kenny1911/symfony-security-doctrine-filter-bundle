<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Parameter;

final class FilterCondition
{
    public readonly Expr\Comparison|Expr\Func|Expr\Andx|Expr\Orx|string $condition;

    /** @var list<Expr\Join> */
    public readonly array $joins;

    /** @var list<Parameter> */
    public readonly array $parameters;

    /**
     * @param list<Expr\Join> $joins
     * @param list<Parameter> $parameters
     */
    public function __construct(
        Expr\Orx|Expr\Andx|string|Expr\Func|Expr\Comparison $condition,
        array $joins = [],
        array $parameters = [],
    ) {
        $this->condition = $condition;
        $this->joins = $joins;
        $this->parameters = $parameters;
    }
}
