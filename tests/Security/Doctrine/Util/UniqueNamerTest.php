<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Security\Doctrine\Util;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\ReadableQueryBuilder;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\Util\UniqueNamer;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\Shop;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UniqueNamerTest extends TestCase
{
    /**
     * @param list<string> $params
     */
    #[DataProvider(methodName: 'dataUniqueParamName')]
    public function testUniqueParamName(string $expectedName, int|string $name, array $params = []): void
    {
        /** @var EntityManagerInterface $em */
        $em = Bootstrap::initDb()->getManagerForClass(Shop::class);
        $qb = new QueryBuilder($em);

        foreach ($params as $param) {
            $qb->setParameter($param, 'value');
        }

        $this->assertSame(
            $expectedName,
            UniqueNamer::uniqueParamName(new ReadableQueryBuilder($qb), $name),
        );
    }

    public static function dataUniqueParamName(): array
    {
        return [
            'int, no other params' => ['5', 5],
            'string, no other params' => ['foo', 'foo'],
            'int' => ['5', 5, [1, 2, 3]],
            'string' => ['foo', 'foo', ['bar', 'baz']],
            'int, has same param' => ['3', 1, [1, 2]],
            'string, has same param' => ['foo2', 'foo', ['foo', 'foo1']],
        ];
    }

    /**
     * @param list<string> $aliases
     */
    #[DataProvider(methodName: 'dataUniqueAlias')]
    public function testUniqueAlias(string $expectedAlias, string $alias, string $rootAlias, array $aliases = []): void
    {
        /** @var EntityManagerInterface $em */
        $em = Bootstrap::initDb()->getManagerForClass(Shop::class);
        $qb = new QueryBuilder($em);
        $qb->from(Shop::class, $rootAlias)->select($rootAlias);

        foreach ($aliases as $joinAlias) {
            $qb->join($rootAlias.'.employees', $joinAlias);
        }

        $this->assertSame(
            $expectedAlias,
            UniqueNamer::uniqueAlias(new ReadableQueryBuilder($qb), $alias),
        );
    }

    public static function dataUniqueAlias(): array
    {
        return [
            'simple' => ['foo', 'foo', 'u'],
            'simple with joins' => ['foo', 'foo', 'u', ['s', 's2', 's3']],
            'unique select alias' => ['u2', 'u', 'u', ['s', 's2', 's3', 'u1']],
            'unique join alias' => ['s4', 's', 'u', ['s', 's1', 's2', 's3']],
        ];
    }
}
