<?php

declare(strict_types=1);

namespace DoctrineORMModule\Service;

use Doctrine\Migrations\Configuration\Configuration;
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
        $connection       = $container->get('doctrine.connection.' . $name);
        $appConfig        = $container->get('config');
        $migrationsConfig = $appConfig['doctrine']['migrations_configuration'][$name];

        $configuration = new Configuration($connection);
        $output        = new ConsoleOutput();

        // Build table metadata storage configuration
        $metadataStorageConfiguration = new TableMetadataStorageConfiguration();
        $metadataStorageConfiguration->setTableName(
            $migrationsConfig['table_storage']['table_name'] ?? 'DoctrineMigrationVersions'
        );
        $metadataStorageConfiguration->setVersionColumnName(
            $migrationsConfig['table_storage']['version_column_name'] ?? 'version'
        );
        $metadataStorageConfiguration->setVersionColumnLength(
            $migrationsConfig['table_storage']['version_column_length'] ?? 1024
        );
        $metadataStorageConfiguration->setExecutedAtColumnName(
            $migrationsConfig['table_storage']['executed_at_column_name'] ?? 'executedAt'
        );
        $metadataStorageConfiguration->setExecutionTimeColumnName(
            $migrationsConfig['table_storage']['execution_time_column_name'] ?? 'executionTime'
        );

        // Configure frozen flag first
        if ($migrationsConfig['frozen'] ?? false) {
            $configuration->freeze();
        }

        // organized_by_year_and_month sets organized_by_year to true too if set
        // so verifying these are distinct is not necessary.
        if ($migrationsConfig['organized_by_year'] ?? false) {
            $configuration->setMigrationsAreOrganizedByYear(true);
        }

        if ($migrationsConfig['organized_by_year_and_month'] ?? false) {
            $configuration->setMigrationsAreOrganizedByYearAndMonth(true);
        }

        $configuration->setMetadataStorageConfiguration($metadataStorageConfiguration);
        $configuration->setAllOrNothing($migrationsConfig['all_or_nothing'] ?? true);
        $configuration->setCheckDatabasePlatform($migrationsConfig['check_database_platform'] ?? true);
        $configuration->setCustomTemplate($migrationsConfig['custom_template'] ?? null);

        if (isset($migrationsConfig['migrations_paths'])) {
            foreach ($migrationsConfig['migrations_paths'] as $namespace => $path) {
                $configuration->addMigrationsDirectory($namespace, $path);
            }
        }

        if (isset($migrationsConfig['migrations_classes'])) {
            foreach ($migrationsConfig['migrations_classes'] as $className) {
                $configuration->addMigrationClass($className);
            }
        }

        return $configuration;
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
