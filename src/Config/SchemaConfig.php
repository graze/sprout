<?php

namespace Graze\Sprout\Config;

use Graze\ConfigValidation\ConfigValidatorInterface;
use Graze\ConfigValidation\Validate;
use Respect\Validation\Validator as v;

class SchemaConfig implements SchemaConfigInterface
{
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
         $this->schema = $options['schema'];
         $this->exclude = $options['exclude'];
         $this->connection = new ConnectionConfig($options['connection']);
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function getConnection(): ConnectionConfigInterface
    {
        return $this->connection;
    }

    public static function getValidator(): ConfigValidatorInterface
    {
        return Validate::arr(false)
                       ->required('connection', v::arrayType()->addRule(
                           ConnectionConfig::getValidator()->getValidator()
                       ))
                       ->required('schema', v::stringType())
                       ->optional('exclude', v::arrayVal()->each(v::stringType()), []);
    }

    /**
     * @return string[]
     */
    public function getExcludes(): array
    {
        return $this->exclude;
    }
}
