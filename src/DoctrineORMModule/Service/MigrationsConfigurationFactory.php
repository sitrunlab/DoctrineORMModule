<?php

declare(strict_types=1);

namespace DoctrineORMModule\Service;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use DoctrineModule\Service\AbstractFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * DBAL Connection ServiceManager factory
 */
class MigrationsConfigurationFactory extends AbstractFactory
{
    /**
     * {@inheritDoc}
     *
     * @return Configuration
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $name             = $this->getName();
        $appConfig        = $container->get('config');
        $migrationsConfig = $appConfig['doctrine']['migrations_configuration'][$name];

        return (new ConfigurationArray($migrationsConfig))->getConfiguration();
    }

    /**
     * {@inheritDoc}
     *
     * @return Configuration
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, Configuration::class);
    }

    public function getOptionsClass(): string
    {
    }
}
