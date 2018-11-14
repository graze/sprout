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

namespace Graze\Sprout\Chop;

use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;

interface TableChopperInterface
{
    /**
     * Truncate all the provided tables in the given schema
     *
     * @param Schema $schema
     * @param Table  ...$tables
     *
     * @return void
     */
    public function chop(Schema $schema, Table ...$tables);
}
