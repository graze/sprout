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

namespace Graze\Sprout;

use Graze\Sprout\Db\Schema;

interface TablePopulatorInterface
{
    /**
     * Populate the tables in a `Schema`. If there are no actual tables, return `null`
     *
     * @param Schema $schema
     *
     * @return Schema|null
     */
    public function populateTables(Schema $schema);
}
