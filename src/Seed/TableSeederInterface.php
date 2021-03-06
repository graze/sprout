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

namespace Graze\Sprout\Seed;

interface TableSeederInterface
{
    /**
     * Take a file, and write the contents into the table within the specified schema
     *
     * @param string $file
     * @param string $schema
     * @param string $table
     *
     * @return void
     */
    public function seed(string $file, string $schema, string $table);
}
