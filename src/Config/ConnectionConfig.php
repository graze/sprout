<?php
/**
 * This file is part of graze/sprout.
 *
 * Copyright © 2018 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/sprout/blob/master/LICENSE.md
 * @link    https://github.com/graze/sprout
 */

namespace Graze\Sprout\Config;

use Graze\ConfigValidation\ConfigValidatorInterface;
use Graze\ConfigValidation\Validate;
use Respect\Validation\Validator as v;

class ConnectionConfig implements ConnectionConfigInterface
{
    const CONFIG_DRIVER        = 'driver';
    const CONFIG_HOST          = 'host';
    const CONFIG_PORT          = 'port';
    const CONFIG_USER          = 'user';
    const CONFIG_PASSWORD      = 'password';
    const CONFIG_DATABASE_NAME = 'dbName';

    /** @var array */
    private $options;

    /**
     * @param array $options
     *
     * @throws \Graze\ConfigValidation\Exceptions\ConfigValidationFailedException
     */
    public function __construct(array $options = [])
    {
        $this->options = static::getValidator()->validate($options);
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->options[static::CONFIG_DRIVER];
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->options[static::CONFIG_HOST];
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return (int) $this->options[static::CONFIG_PORT];
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->options[static::CONFIG_USER];
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->options[static::CONFIG_PASSWORD];
    }

    /**
     * @return string
     */
    public function getDbName(): string
    {
        return $this->options['dbName'];
    }

    /**
     * @return ConfigValidatorInterface
     */
    public static function getValidator(): ConfigValidatorInterface
    {
        return Validate::arr(false)
                       ->required(
                           'driver',
                           v::stringType()->in([ConnectionConfigInterface::DRIVER_MYSQL])
                       )
                       ->required('user', v::stringType())
                       ->required('password', v::stringType())
                       ->required('host', v::stringType())
                       ->optional('port', v::intVal(), 3306)
                       ->optional('dbName', v::stringType());
    }

    /**
     * @return string
     */
    public function getDsn(): string
    {
        return sprintf(
            '%s:dnbame=%s;host=%s;port=%d',
            $this->getDriver(),
            $this->getDbName(),
            $this->getHost(),
            $this->getPort()
        );
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return sprintf(
            '%s://%s:%s@%s:%d/%s',
            $this->getDriver(),
            $this->getUser(),
            $this->getPassword(),
            $this->getHost(),
            $this->getPort(),
            $this->getDbName()
        );
    }
}
