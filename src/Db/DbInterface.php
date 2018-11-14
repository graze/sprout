<?php

namespace Graze\Sprout\Db;

use Traversable;

interface DbInterface
{
    const INSERT_DUPLICATE_FAIL   = 'fail';
    const INSERT_DUPLICATE_IGNORE = 'ignore';
    const INSERT_DUPLICATE_UPDATE = 'update';

    /**
     * @param string $table
     * @param array  $data
     * @param string $duplicate
     *
     * @return int The number of rows inserted
     */
    public function insert(string $table, array $data, string $duplicate = self::INSERT_DUPLICATE_FAIL): int;

    /**
     * @param string $table
     * @param array  $where
     *
     * @return int The number of rows deleted
     */
    public function delete(string $table, array $where): int;

    /**
     * Truncate a table
     *
     * @param string $table
     *
     * @return void
     */
    public function truncate(string $table);

    /**
     * @param string $table
     *
     * @return Traversable
     */
    public function getFields(string $table): Traversable;

    /**
     * @param string $table
     * @param array  $where
     *
     * @return Traversable
     */
    public function fetch(string $table, array $where = []): Traversable;
}
