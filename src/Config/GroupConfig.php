<?php

namespace Graze\Sprout\Config;

use Graze\ConfigValidation\ConfigValidatorInterface;
use Graze\ConfigValidation\Validate;
use Respect\Validation\Validator as v;

class GroupConfig implements GroupConfigInterface
{
    const CONFIG_PATH = 'path';

    /** @var string */
    private $path;

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
        $this->path = $options[static::CONFIG_PATH];
    }

    /**
     * @return ConfigValidatorInterface
     */
    public static function getValidator(): ConfigValidatorInterface
    {
        return Validate::arr(false)
                       ->required(static::CONFIG_PATH, v::stringType());
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
