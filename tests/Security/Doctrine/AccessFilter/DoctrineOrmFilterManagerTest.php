<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Security\Doctrine\AccessFilter;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\FilterSubject;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\DoctrineOrmFilterManager;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\Shop;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DoctrineOrmFilterManagerTest extends TestCase
{
    /**
     * @dataProvider data
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    #[DataProvider(methodName: 'data')]
    public function test(string $attribute, int $userId, array $expectedShopIds, string $expectedDQL): void
    {
        // Bootstrap
        $em = Bootstrap::initDb();
        Bootstrap::fillDb($em);
        $filterManager = new DoctrineOrmFilterManager([new ShopFilter()]);

        // Prepare data
        $qb = $em->createQueryBuilder()->from(Shop::class, 's')->select('s');
        $user = $em->find(User::class, $userId) ?? throw new Exception('User not found.');

        // Run filter
        $filterManager->filter($attribute, $qb, new FilterSubject(Shop::class, 's'), $user);

        // Prepare results
        $dql = (string) $qb->getQuery()->getDQL();
        $filterUserId = $qb->getParameter('filterUserId')?->getValue();
        /** @var list<Shop> $shops */
        $shops = $qb->getQuery()->getResult();

        // Assert checks
        $this->assertSame(self::trimDQL($expectedDQL), self::trimDQL($dql));
        $this->assertSame($userId, $filterUserId);
        $this->assertCount(count($expectedShopIds), $shops);

        foreach ($shops as $shop) {
            $this->assertContains($shop->getId(), $expectedShopIds);
        }
    }

    public static function data(): array
    {
        $readDQL = <<<DQL
        SELECT s
        FROM Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\Shop s
        INNER JOIN s.owner filterShopOwner
        INNER JOIN s.employees filterShopEmployee
        WHERE (filterShopOwner.id = :filterUserId OR filterShopEmployee.id = :filterUserId)
        DQL;

        $writeDQL = <<<DQL
        SELECT s
        FROM Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\Shop s
        INNER JOIN s.owner filterShopOwner
        WHERE filterShopOwner.id = :filterUserId
        DQL;

        return [
            [ShopFilter::ATTRIBUTE_SHOP_READ, 1, [1], $readDQL],
            [ShopFilter::ATTRIBUTE_SHOP_READ, 2, [1, 2], $readDQL],
            [ShopFilter::ATTRIBUTE_SHOP_READ, 3, [2], $readDQL],
            [ShopFilter::ATTRIBUTE_SHOP_EDIT, 1, [1], $writeDQL],
            [ShopFilter::ATTRIBUTE_SHOP_EDIT, 2, [], $writeDQL],
            [ShopFilter::ATTRIBUTE_SHOP_EDIT, 3, [2], $writeDQL],
        ];
    }

    private static function trimDQL(string $dql): string
    {
        return trim(preg_replace('/\s+/', ' ', $dql));
    }
}
