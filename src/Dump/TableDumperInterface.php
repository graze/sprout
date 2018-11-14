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

namespace Graze\Sprout\Dump;

use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;

interface TableDumperInterface
{
    /**
     * @param Schema $schema
     * @param Table  $table
     *
     * @return void
     */
    public function dump(Schema $schema, Table $table);
}
