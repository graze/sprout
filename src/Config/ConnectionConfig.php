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

namespace Graze\Sprout\Config;

use Graze\ConfigValidation\ConfigValidatorInterface;
use Graze\ConfigValidation\Validate;
use Respect\Validation\Validator as v;

class ConnectionConfig implements ConnectionConfigInterface
{
    /**
     * @var array
     */
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
        return $this->options['driver'];
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->options['host'];
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return (int)$this->options['port'];
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->options['user'];
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->options['password'];
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
                       ->required('dbName', v::stringType());
    }
}
