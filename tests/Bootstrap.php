<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\Shop;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\User;

final class Bootstrap
{
    public static function initDb(): EntityManagerInterface
    {
        $config = ORMSetup::createAttributeMetadataConfiguration([__DIR__.'/Bootstrap/Entity'], true);
        $connection = DriverManager::getConnection([
            'driver' => 'sqlite3',
            'memory' => true,
        ], $config);

        $em = new EntityManager($connection, $config);

        (new SchemaTool($em))->updateSchema($em->getMetadataFactory()->getAllMetadata());

        return $em;
    }

    public static function fillDb(EntityManagerInterface $em): void
    {
        $entities = [];

        // Fill users
        $entities[] = $shop1Owner = new User(1, 'Shop1 Owner');
        $entities[] = $shop1Employee = $shop2Employee = new User(2, 'Shop1 employee');
        $entities[] = $shop2Owner = new User(3, 'Shop2 Owner');

        // Fill shops
        $entities[] = (new Shop(1, $shop1Owner))->addEmployee($shop1Employee);
        $entities[] = (new Shop(2, $shop2Owner))->addEmployee($shop2Employee);

        foreach ($entities as $entity) {
            $em->persist($entity);
        }

        $em->flush();
    }
}
