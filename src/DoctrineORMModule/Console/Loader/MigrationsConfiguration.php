<?php

declare(strict_types=1);

namespace DoctrineORMModule\Console\Loader;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader;
use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;

class MigrationsConfiguration implements ConfigurationLoader
{
    /** @var array */
    protected $configurations;

    /** @var string */
    protected $defaultObjectManagerName = 'doctrine.entitymanager.orm_default';

    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    public function getConfiguration(): Configuration
    {
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

        return (new ConfigurationArray($this->configurations[$matches['serviceName']]))->getConfiguration();
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
