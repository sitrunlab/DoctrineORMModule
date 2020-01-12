<?php

declare(strict_types=1);

namespace DoctrineORMModule\Options;

use Laminas\Stdlib\AbstractOptions;

/**
 * Configuration options for a DBAL Connection
 *
 * @link    http://www.doctrine-project.org/
 */
class DBALConfiguration extends AbstractOptions
{
    /**
     * Set the cache key for the result cache. Cache key
     * is assembled as "doctrine.cache.{key}" and pulled from
     * service locator.
     */
    protected string $resultCache = 'array';

    /**
     * Set the class name of the SQL Logger, or null, to disable.
     */
    protected ?string $sqlLogger = null;

    /**
     * Keys must be the name of the type identifier and value is
     * the class name of the Type
     *
     * @var string[]
     */
    protected array $types = [];

    public function setResultCache(string $resultCache) : self
    {
        $this->resultCache = $resultCache;

        return $this;
    }

    public function getResultCache() : string
    {
        return 'doctrine.cache.' . $this->resultCache;
    }

    public function setSqlLogger(string $sqlLogger) : void
    {
        $this->sqlLogger = $sqlLogger;
    }

    public function getSqlLogger() : ?string
    {
        return $this->sqlLogger;
    }

    /**
     * @param string[] $types
     */
    public function setTypes(array $types) : self
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTypes() : array
    {
        return $this->types;
    }
}
