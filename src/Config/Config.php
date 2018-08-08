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
use Graze\Sprout\Config\ConnectionConfig;
use Graze\Sprout\Config\GroupConfig;
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
    const DEFAULT_CONFIG_PATH = 'config/sprout.yml';
    const DEFAULT_PROCESSES   = 10;

    const CONFIG_DEFAULT_GROUP                  = 'defaults.group';
    const CONFIG_DEFAULT_PATH                   = 'defaults.path';
    const CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES = 'defaults.simultaneousProcesses';

    const CONFIG_SCHEMAS = 'schemas';

    const CONFIG_GROUPS = 'groups';

    /** @var array */
    private $config;

    /** @var ConfigValidatorInterface */
    private $validator;

    public function __construct()
    {
        $this->validator =
            Validate::arr(false)
                    ->optional(static::CONFIG_DEFAULT_GROUP, v::stringType()->alnum('_'), static::DEFAULT_GROUP)
                    ->optional(static::CONFIG_DEFAULT_PATH, v::stringType()->directory(), static::DEFAULT_PATH)
                    ->optional(static::CONFIG_DEFAULT_SIMULTANEOUS_PROCESSES, v::intVal(), static::DEFAULT_PROCESSES)
                    ->optional(
                        static::CONFIG_GROUPS,
                        v::arrayVal()->each(
                            GroupConfig::getValidator()
                                       ->getValidator()
                        )
                    )
                    ->required(
                        static::CONFIG_SCHEMAS,
                        v::arrayVal()->length(1, null)->each(
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
     * @throws \Graze\ConfigValidation\Exceptions\ConfigValidationFailedException
     */
    public function parse(string $path): Config
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf('The supplied path %s does not exist', $path));
        }

        $parser = new Parser();
        $fileConfig = $parser->parse(file_get_contents($path));

        $config = $this->validator->validate($fileConfig);

        // populates the schema / connection.dbname properties for each defined schema if not set
        $schemas = $config['schemas'];
        foreach ($schemas as $schema => $value) {
            // TODO: remove these when config-validator is updated to handle child builders
            $value = SchemaConfig::getValidator()->validate($value);
            $value['connection'] = ConnectionConfig::getValidator()->validate($value['connection']);

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
    public function get(string $keyPath, $default = null)
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
     * @param SchemaConfigInterface $schema The schema configuration
     * @param string|null           $group  The group. If none supplied, uses the default
     *
     * @return string
     */
    public function getSchemaPath(SchemaConfigInterface $schema, string $group = null): string
    {
        $group = $group ?: $this->get("defaults.group");
        return sprintf('%s/%s/', $this->getGroupPath($group), $schema->getDirName());
    }

    /**
     * @param string $group
     *
     * @return string
     */
    public function getGroupPath(string $group): string
    {
        $configGroup = $this->get("groups.{$group}");
        if (!is_null($configGroup)) {
            return $configGroup['path'];
        } else {
            return sprintf('%s/%s/', $this->get(static::CONFIG_DEFAULT_PATH), $group);
        }
    }
}
