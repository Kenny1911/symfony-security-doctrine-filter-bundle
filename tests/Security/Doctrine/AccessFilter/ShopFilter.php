<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Security\Doctrine\AccessFilter;

use InvalidArgumentException;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\BaseFilter;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\FilterCondition;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\FilterSubject;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\Shop;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\User;

final class ShopFilter extends BaseFilter
{
    public const ATTRIBUTE_SHOP_READ = 'shop.read';
    public const ATTRIBUTE_SHOP_EDIT = 'shop.edit';

    protected function supports(string $attribute, FilterSubject $subject, mixed $user): bool
    {
        return in_array($attribute, [self::ATTRIBUTE_SHOP_READ, self::ATTRIBUTE_SHOP_EDIT], true)
            && Shop::class === $subject->getClassName()
            && $user instanceof User
        ;
    }

    protected function doApply(string $attribute, FilterSubject $subject, mixed $user): array
    {
        if (!$user instanceof User) {
            throw new InvalidArgumentException('Invalid user.');
        }

        return match ($attribute) {
            self::ATTRIBUTE_SHOP_READ => $this->applyShopRead($subject, $user),
            self::ATTRIBUTE_SHOP_EDIT => $this->applyShopEdit($subject, $user),
        };
    }

    /**
     * @return list<FilterCondition>
     */
    private function applyShopRead(FilterSubject $subject, User $user): array
    {
        $alias = $subject->getAlias();
        $conditions = [];

        $conditions[] = $this->createConditionBuilder()
            ->innerJoin($alias.'.owner', 'filterShopOwner')
            ->innerJoin($alias.'.employees', 'filterShopEmployee')
            ->setCondition('(filterShopOwner.id = :filterUserId OR filterShopEmployee.id = :filterUserId)')
            ->setParameter('filterUserId', $user->getId())
            ->build()
        ;

        return $conditions;
    }

    /**
     * @return list<FilterCondition>
     */
    private function applyShopEdit(FilterSubject $subject, User $user): array
    {
        $alias = $subject->getAlias();
        $conditions = [];

        $conditions[] = $this->createConditionBuilder()
            ->innerJoin($alias.'.owner', 'filterShopOwner')
            ->setCondition('filterShopOwner.id = :filterUserId')
            ->setParameter('filterUserId', $user->getId())
            ->build()
        ;

        return $conditions;
    }
}
