<?php
/**
 * This file is part of graze/sprout.
 *
 * Copyright (c) 2017 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/sprout/blob/master/LICENSE.md
 * @link    https://github.com/graze/sprout
 */

namespace Graze\Sprout;

use Graze\ConfigValidation\ConfigValidatorInterface;
use Graze\ConfigValidation\Validate;
use Graze\Sprout\Config\SchemaConfig;
use Graze\Sprout\Config\SchemaConfigInterface;
use InvalidArgumentException;
use Respect\Validation\Validator as v;
use Symfony\Component\Yaml\Parser;

/**
 * Config contains all the configuration required for this application
 */
class Config
{
    const DEFAULT_GROUP       = 'core';
    const DEFAULT_PATH        = '/seed';
    const DEFAULT_CONFIG_PATH = '/app/config/config.yml';
    const DEFAULT_PROCESSES   = 10;

    /** @var array */
    private $config;

    /** @var ConfigValidatorInterface */
    private $validator;

    public function __construct()
    {
        $this->validator = Validate::arr(false)
                                   ->optional('defaults.group', v::stringType()->alnum('_'), static::DEFAULT_GROUP)
                                   ->optional('defaults.path', v::stringType()->directory(), static::DEFAULT_PATH)
                                   ->optional('defaults.simultaneousProcesses', v::intVal(), static::DEFAULT_PROCESSES)
                                   ->required(
                                       'schemas',
                                       v::arrayVal()->each(
                                           SchemaConfig::getValidator()
                                                       ->getValidator()
                                       )
                                   );
    }

    /**
     * Parse the config file provided in the constructor.
     *
     * @param string $path
     *
     * @return $this
     */
    public function parse(string $path)
    {
        $parser = new Parser();
        $fileConfig = $parser->parse(file_get_contents($path));

        $config = $this->validator->validate($fileConfig);

        // populates the schema / connection.dbname properties for each defined schema if not set
        $schemas = $this->config['schemas'];
        foreach ($schemas as $schema => $value) {
            if (is_null($value['schema'])) {
                $config['schemas'][$schema]['schema'] = $schema;
            }
            if (is_null($value['connection']['dbName'])) {
                $config['schemas'][$schema]['connection']['dbName'] = $schema;
            }
            $config['schemas'][$schema] = new SchemaConfig($config['schemas'][$schema]);
        }

        $this->config = $config;

        return $this;
    }

    /**
     * Get an element from the configuration
     *
     * @param string     $keyPath A dot separated identifier describing the name of the key to retrieve.
     *                            `get('defaults.path', '/some/path')` will return `$config['defaults']['path']` or
     *                            `'/some/path'` if it doesn't exist
     * @param mixed|null $default The default value to use if the field does not exist or is null. Note
     *
     * @return mixed the value of an item
     */
    public function get(string $keyPath, $default = null): mixed
    {
        $paths = explode('.', $keyPath);
        $cur = $this->config;
        $trail = '';
        foreach ($paths as $path) {
            $trail .= '.' . $path;
            if (!isset($cur[$path]) || is_null($cur[$path])) {
                return $default;
            }
            $cur = $cur[$path];
        }
        return $cur;
    }

    /**
     * @param string $schema
     *
     * @return SchemaConfigInterface
     */
    public function getSchemaConfiguration(string $schema): SchemaConfigInterface
    {
        $value = $this->get("schemas.{$schema}");
        if (is_null($value)) {
            throw new InvalidArgumentException("no schema: {$schema} found in configuration");
        }
        return $value;
    }

    /**
     * Get the path for a specific schema
     *
     * @param string      $schema The schema configuration to get
     * @param string|null $group  The group. If none supplied, uses the default
     *
     * @return string
     */
    public function getSchemaPath(string $schema, string $group = null): string
    {
        $group = $group ?: $this->get("default.group");
        $path = $this->get("default.path");

        return sprintf('%s/%s/%s/', $path, $group, $schema);
    }
}
