<?php

declare(strict_types=1);

namespace DoctrineORMModule\Service;

use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Symfony\Component\Console\Input\ArgvInput;

use function class_exists;
use function strtolower;
use function ucfirst;

/**
 * Service factory for migrations command
 */
class MigrationsCommandFactory implements FactoryInterface
{
    /** @var string */
    private $name;

    /** @var string */
    private $defaultObjectManagerName = 'doctrine.entitymanager.orm_default';

    public function __construct(string $name)
    {
        $this->name = ucfirst(strtolower($name));
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
        $className = 'Doctrine\Migrations\Tools\Console\Command\\' . $this->name . 'Command';

        if (! class_exists($className)) {
            throw new InvalidArgumentException();
        }

        $config = $container->get('config');
        $objectManagerName = $this->getObjectManagerName();

        // Copied from DoctrineModule/ServiceFactory/AbstractDoctrineServiceFactory
        if (
            ! preg_match(
                '/^doctrine\.((?<mappingType>orm|odm)\.|)(?<serviceType>[a-z0-9_]+)\.(?<serviceName>[a-z0-9_]+)$/',
                $objectManagerName,
                $matches
            )
        ) {
            throw new RuntimeException('The object manager alias is invalid: ' . $objectManagerAlias);
        }

        return new $className(
            DependencyFactory::fromEntityManager(
                new ConfigurationArray($config['doctrine']['migrations_configuration'][$matches['serviceName']]),
                new ExistingEntityManager($container->get($objectManagerName))
            )
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createService(ServiceLocatorInterface $container): DoctrineCommand
    {
        return $this($container, 'Doctrine\Migrations\Tools\Console\Command\\' . $this->name . 'Command');
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
