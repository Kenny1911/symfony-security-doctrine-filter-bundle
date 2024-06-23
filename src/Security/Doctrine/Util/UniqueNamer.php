<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\Util;

use Doctrine\ORM\Query\Parameter;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\ReadableQueryBuilder;

final class UniqueNamer
{
    public static function uniqueParamName(ReadableQueryBuilder $qb, int|string $name, int $index = 1): string
    {
        if (is_int($name)) {
            return self::uniqueIntParamName($qb, $name);
        }

        return self::uniqueStringParamName($qb, $name);
    }

    public static function uniqueIntParamName(ReadableQueryBuilder $qb, int $name): string
    {
        while ($qb->getParameter($name)) {
            ++$name;
        }

        return Parameter::normalizeName($name);
    }

    public static function uniqueStringParamName(ReadableQueryBuilder $qb, string $name): string
    {
        $name = Parameter::normalizeName($name);

        if (!$qb->getParameter($name)) {
            return $name;
        }

        $index = 1;

        while ($qb->getParameter($name.$index)) {
            ++$index;
        }

        return $name.$index;
    }

    public static function uniqueAlias(ReadableQueryBuilder $qb, string $alias): string
    {
        $alias = trim($alias);
        $aliases = $qb->getAllAliases();

        if (!in_array($alias, $aliases, true)) {
            return $alias;
        }

        $index = 1;

        while (in_array($alias.$index, $aliases, true)) {
            ++$index;
        }

        return $alias.$index;
    }
}
