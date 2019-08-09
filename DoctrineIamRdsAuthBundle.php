<?php

namespace App\Lead\DoctrineIamRdsAuthBundle;

use App\Lead\DoctrineIamRdsAuthBundle\DependencyInjection\OverrideDoctrineCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle for auth user in RDS database using IAM (instead of username/password pair)
 */
class DoctrineIamRdsAuthBundle extends Bundle
{
    public const VERSION = '0.0.1';

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new OverrideDoctrineCompilerPass());
    }
}