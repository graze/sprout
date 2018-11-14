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

class SeedData implements SeedDataInterface
{
    /** @var string */
    private $table;
    /** @var array */
    private $data;
    /** @var string */
    private $seedType;

    /**
     * SeedData constructor.
     *
     * @param string $table
     * @param array  $data
     * @param string $seedType
     */
    public function __construct(string $table, array $data, string $seedType = self::SEED_TYPE_TRUNCATE)
    {
        $this->table = $table;
        $this->data = $data;
        $this->seedType = $seedType;
    }

    /**
     * Collection of rows in `field => value` format
     *
     * @return array [[field => value, ...], ...]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     * How to seed this data.
     *
     * - *SEED_TYPE_TRUNCATE*: truncate the table before seeding
     * - *SEED_TYPE_UPDATE*: update any existing rows that have a common primary/unique ids
     * - *SEED_TYPE_IGNORE*: ignore the new data for any rows that have common primary/unique ids
     *
     * @return string
     */
    public function getSeedType(): string
    {
        return $this->seedType;
    }
}
