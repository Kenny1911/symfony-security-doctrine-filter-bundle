<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Security\Core\Authorization\Voter;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Core\Authorization\Voter\FilterVoter;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\DoctrineOrmFilterManager;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\Shop;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\User;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Security\Doctrine\AccessFilter\ShopFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Throwable;

final class FilterVoterTest extends TestCase
{
    /**
     * @psalm-param VoterInterface::ACCESS_* $expectedAccess
     *
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     * @throws Throwable
     */
    #[DataProvider(methodName: 'data')]
    public function test(int $expectedAccess, int $userId, int $shopId, string $attribute): void
    {
        // Bootstrap
        $doctrine = Bootstrap::initDb();
        Bootstrap::fillDb($doctrine);

        // Init voter
        $voter = new FilterVoter($doctrine, new DoctrineOrmFilterManager([new ShopFilter()]), false);

        // Prepare data
        $em = $doctrine->getManager();
        $user = $em->find(User::class, $userId) ?? throw new Exception('User not found.');
        $token = $this->createToken($user);
        $shop = $em->find(Shop::class, $shopId) ?? throw new Exception('Shop not found.');

        // Assert voter access
        $this->assertSame(
            $expectedAccess,
            $voter->vote($token, $shop, [$attribute]),
        );
    }

    public static function data(): array
    {
        return [
            'unknown attribute' => [VoterInterface::ACCESS_ABSTAIN, 1, 1, 'invalid'],
            // Check User 1 access
            [VoterInterface::ACCESS_GRANTED, 1, 1, ShopFilter::ATTRIBUTE_SHOP_READ],
            [VoterInterface::ACCESS_GRANTED, 1, 1, ShopFilter::ATTRIBUTE_SHOP_EDIT],
            [VoterInterface::ACCESS_DENIED, 1, 2, ShopFilter::ATTRIBUTE_SHOP_READ],
            [VoterInterface::ACCESS_DENIED, 1, 2, ShopFilter::ATTRIBUTE_SHOP_EDIT],
            // Check User 2 access
            [VoterInterface::ACCESS_GRANTED, 2, 1, ShopFilter::ATTRIBUTE_SHOP_READ],
            [VoterInterface::ACCESS_DENIED, 2, 1, ShopFilter::ATTRIBUTE_SHOP_EDIT],
            [VoterInterface::ACCESS_GRANTED, 2, 2, ShopFilter::ATTRIBUTE_SHOP_READ],
            [VoterInterface::ACCESS_DENIED, 2, 2, ShopFilter::ATTRIBUTE_SHOP_EDIT],
            // Check User 3 access
            [VoterInterface::ACCESS_DENIED, 3, 1, ShopFilter::ATTRIBUTE_SHOP_READ],
            [VoterInterface::ACCESS_DENIED, 3, 1, ShopFilter::ATTRIBUTE_SHOP_EDIT],
            [VoterInterface::ACCESS_GRANTED, 3, 2, ShopFilter::ATTRIBUTE_SHOP_READ],
            [VoterInterface::ACCESS_GRANTED, 3, 2, ShopFilter::ATTRIBUTE_SHOP_EDIT],
        ];
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     * @throws Throwable
     */
    #[DataProvider(methodName: 'dataInvalidSubject')]
    public function testInvalidSubject(mixed $subject): void
    {
        // Bootstrap
        $doctrine = Bootstrap::initDb();
        Bootstrap::fillDb($doctrine);

        // Init voter
        $voter = new FilterVoter($doctrine, new DoctrineOrmFilterManager([new ShopFilter()]), false);

        // Prepare data
        $em = $doctrine->getManager();
        $user = $em->find(User::class, 1) ?? throw new Exception('User not found.');
        $token = $this->createToken($user);

        // Assert voter access
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, $subject, [ShopFilter::ATTRIBUTE_SHOP_READ])
        );
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, $subject, [ShopFilter::ATTRIBUTE_SHOP_EDIT])
        );
    }

    public static function dataInvalidSubject(): array
    {
        return [
            'subject is not object type' => ['invalid subject'],
            'subject is not doctrine entity' => [new stdClass()],
        ];
    }

    private function createToken(User $user): TokenInterface
    {
        $token = new class() extends AbstractToken {};
        $token->setUser($user);

        return $token;
    }
}
