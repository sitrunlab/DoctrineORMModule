<?php

declare(strict_types=1);

namespace DoctrineORMModule\Service;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;

use function class_exists;
use function preg_match;
use function strtolower;
use function ucfirst;

/**
 * Service factory for migrations command
 */
class MigrationsCommandFactory implements FactoryInterface
{
    /** @var string */
    private $commandClassName;

    /** @var string */
    private $defaultObjectManagerName = 'doctrine.entitymanager.orm_default';

    public function __construct(string $name)
    {
        // The migrations:sync-metadata-storage does not follow the same 1:1
        // naming and uses the SyncMetadataCommand.  For this case adjust the name.
        if ($name === 'syncmetadatastorage') {
            $name = 'syncmetadata';
        }

        $this->commandClassName =
            'Doctrine\Migrations\Tools\Console\Command\\'
            . ucfirst(strtolower($name))
            . 'Command';
    }

    /**
     * {@inheritDoc}
     *
     * @return AbstractCommand
     *
     * @throws InvalidArgumentException
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $commandClassName = $this->commandClassName;

        if (! class_exists($commandClassName)) {
            throw new InvalidArgumentException();
        }

        $config            = $container->get('config');
        $objectManagerName = $this->getObjectManagerName();

        // Copied from DoctrineModule/ServiceFactory/AbstractDoctrineServiceFactory
        if (
            ! preg_match(
                '/^doctrine\.((?<mappingType>orm|odm)\.|)(?<serviceType>[a-z0-9_]+)\.(?<serviceName>[a-z0-9_]+)$/',
                $objectManagerName,
                $matches
            )
        ) {
            throw new RuntimeException('The object manager name is invalid: ' . $objectManagerName);
        }

        // An object manager may not have a migrations configuration and that's OK.
        // Use default values in that case.
        return new $commandClassName(
            DependencyFactory::fromEntityManager(
                new ConfigurationArray($config['doctrine']['migrations_configuration'][$matches['serviceName']] ?? []),
                new ExistingEntityManager($container->get($objectManagerName))
            )
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createService(ServiceLocatorInterface $container): DoctrineCommand
    {
        return $this($container, $this->commandClassName);
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
