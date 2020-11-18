<?php

declare(strict_types=1);

namespace DoctrineORMModule\Console\Loader;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\EntityManager\EntityManagerLoader;
use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArgvInput;

class MigrationsEntityManager implements EntityManagerLoader
{
    /** @var ContainerInterface */
    protected $container;

    /** @var string */
    protected $defaultObjectManagerName = 'doctrine.entitymanager.orm_default';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getEntityManager() : EntityManagerInterface
    {
        return $this->container->get($this->getObjectManagerName());
    }

    private function getObjectManagerName(): string
    {
        $arguments = new ArgvInput();

        if (! $arguments->hasParameterOption('--object-manager')) {
            return $this->defaultObjectManagerName;
        }

        return $arguments->getParameterOption('--object-manager');
    }
}
