<?php

namespace Graze\Sprout\Config;

use Graze\ConfigValidation\ConfigValidatorInterface;
use Graze\ConfigValidation\Validate;
use Respect\Validation\Validator as v;

class SchemaConfig implements SchemaConfigInterface
{
    const CONFIG_SCHEMA     = 'schema';
    const CONFIG_EXCLUDE    = 'exclude';
    const CONFIG_CONNECTION = 'connection';

    /** @var string */
    private $schema;
    /** @var string[] */
    private $exclude;
    /** @var ConnectionConfigInterface */
    private $connection;

    /**
     * SchemaConfig constructor.
     *
     * @param array $options Array of options
     *
     * @throws \Graze\ConfigValidation\Exceptions\ConfigValidationFailedException
     */
    public function __construct(array $options = [])
    {
        $options = static::getValidator()->validate($options);
        $this->schema = $options[static::CONFIG_SCHEMA];
        $this->exclude = $options[static::CONFIG_EXCLUDE];
        $this->connection = new ConnectionConfig($options[static::CONFIG_CONNECTION]);
    }

    /**
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @return ConnectionConfigInterface
     */
    public function getConnection(): ConnectionConfigInterface
    {
        return $this->connection;
    }

    /**
     * @return ConfigValidatorInterface
     */
    public static function getValidator(): ConfigValidatorInterface
    {
        return Validate::arr(false)
                       ->addChild(static::CONFIG_CONNECTION, ConnectionConfig::getValidator())
                       ->optional(static::CONFIG_SCHEMA, v::stringType())
                       ->optional(static::CONFIG_EXCLUDE, v::arrayVal()->each(v::stringType()), []);
    }

    /**
     * @return string[]
     */
    public function getExcludes(): array
    {
        return $this->exclude;
    }
}
