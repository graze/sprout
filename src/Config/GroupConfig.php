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
