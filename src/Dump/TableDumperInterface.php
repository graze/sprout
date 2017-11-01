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

namespace Graze\Sprout\Dump;

interface TableDumperInterface
{
    /**
     * @param string $schema
     * @param string $table
     * @param string $file
     *
     * @return void
     */
    public function dump(string $schema, string $table, string $file);
}
