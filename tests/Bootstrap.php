<?php

declare(strict_types=1);

namespace Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\AbstractManagerRegistry;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Proxy;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\Shop;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Tests\Bootstrap\Entity\User;

final class Bootstrap
{
    public static function initDb(): ManagerRegistry
    {
        $params = [
            'driver' => 'sqlite3',
            'memory' => true,
        ];
        $config = ORMSetup::createAttributeMetadataConfiguration([__DIR__.'/Bootstrap/Entity'], true);
        $doctrine = new class($params, $config) extends AbstractManagerRegistry {
            private Connection $connection;

            private EntityManagerInterface $em;

            public function __construct(array $params, Configuration $config)
            {
                /** @psalm-suppress MixedArgumentTypeCoercion */
                $this->connection = DriverManager::getConnection($params, $config);
                $this->em = new EntityManager($this->connection, $config);

                parent::__construct(
                    'ORM',
                    [
                        'default' => 'connection.default',
                    ],
                    [
                        'default' => 'em.default',
                    ],
                    'default',
                    'default',
                    Proxy::class,
                );
            }

            /** @psalm-suppress InvalidReturnType */
            protected function getService(string $name)
            {
                /** @psalm-suppress InvalidReturnStatement */
                return match ($name) {
                    'connection.default' => $this->connection,
                    'em.default' => $this->em,
                };
            }

            protected function resetService(string $name)
            {
                // noop
            }
        };

        /** @var EntityManagerInterface $em */
        foreach ($doctrine->getManagers() as $em) {
            (new SchemaTool($em))->updateSchema($em->getMetadataFactory()->getAllMetadata());
        }

        return $doctrine;
    }

    public static function fillDb(ManagerRegistry $doctrine): void
    {
        $em = $doctrine->getManager();

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
