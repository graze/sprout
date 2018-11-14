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

interface SeedDataInterface
{
    /**
     * truncate the table before seeding
     */
    const SEED_TYPE_TRUNCATE = 'truncate';
    /**
     * seed that data, and update any existing entries
     */
    const SEED_TYPE_UPDATE = 'update';
    /**
     * seed the data, but ignore any existing entries
     */
    const SEED_TYPE_IGNORE = 'ignore';

    /**
     * Collection of rows in `key=>value` format
     *
     * @return array [[key=>value, ...], ...]
     */
    public function getData(): array;

    /**
     * @return string
     */
    public function getTableName(): string;

    /**
     * How to seed this data.
     *
     * - *SEED_TYPE_TRUNCATE*: truncate the table before seeding
     * - *SEED_TYPE_UPDATE*: update any existing rows that have a common primary/unique ids
     * - *SEED_TYPE_IGNORE*: ignore the new data for any rows that have common primary/unique ids
     *
     * @return string
     */
    public function getSeedType(): string;
}
