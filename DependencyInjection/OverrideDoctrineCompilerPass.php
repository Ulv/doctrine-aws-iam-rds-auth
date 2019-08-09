<?php

namespace App\Lead\DoctrineIamRdsAuthBundle\DependencyInjection;

use App\Lead\DoctrineIamRdsAuthBundle\Factory\DoctrineConnectionFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideDoctrineCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // override doctrine's connection factory class with our own
        $container->setParameter('doctrine.dbal.connection_factory.class',
            DoctrineConnectionFactory::class);
    }
}