<?php

namespace Graze\Sprout\Parser;

use Graze\Sprout\Config\SchemaConfigInterface;

class ParsedSchema
{
    /** @var SchemaConfigInterface */
    private $schemaConfig;
    /** @var string */
    private $path;
    /** @var array */
    private $tables;

    /**
     * ParsedSchema constructor.
     *
     * @param SchemaConfigInterface $schemaConfig
     * @param string                $path
     * @param array                 $tables
     */
    public function __construct(SchemaConfigInterface $schemaConfig, string $path, array $tables)
    {
        $this->schemaConfig = $schemaConfig;
        $this->path = $path;
        $this->tables = $tables;
    }

    /**
     * @return SchemaConfigInterface
     */
    public function getSchemaConfig(): SchemaConfigInterface
    {
        return $this->schemaConfig;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @param array $tables
     *
     * @return ParsedSchema
     */
    public function setTables(array $tables): ParsedSchema
    {
        $this->tables = $tables;
        return $this;
    }

    /**
     * @return string
     */
    public function getSchemaName(): string
    {
        return $this->schemaConfig->getSchema();
    }
}
