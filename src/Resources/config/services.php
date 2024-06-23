<?php

declare(strict_types=1);

use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Core\Authorization\Voter\FilterVoter;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\DoctrineOrmFilterManager;
use Kenny1911\SymfonySecurityDoctrineFilterBundle\Security\Doctrine\AccessFilter\FilterManager;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container) {
    // Filter Manager
    $container->services()
        ->set(DoctrineOrmFilterManager::class)
            ->args([
                tagged_iterator('symfony_security_doctrine_filter.filter'),
            ])
        ->alias(FilterManager::class, DoctrineOrmFilterManager::class)
        ->alias('symfony_security_doctrine_filter.filter_manager', DoctrineOrmFilterManager::class)
    ;

    // Filter Voter
    $container->parameters()
        ->set('symfony_security_doctrine_filter.voter.throws', false)
    ;

    $container->services()
        ->set(FilterVoter::class)
        ->autoconfigure()
        ->args([
            service('doctrine'),
            service(FilterManager::class),
            param('symfony_security_doctrine_filter.voter.throws'),
        ])
    ;
};
