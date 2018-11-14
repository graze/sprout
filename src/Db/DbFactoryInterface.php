<?php

namespace Graze\Sprout\Db;

use Graze\Sprout\Config\SchemaConfigInterface;

interface DbFactoryInterface
{
    /**
     * Get a DB connection from a schema
     *
     * @param SchemaConfigInterface $schema
     *
     * @return DbInterface
     *
     * @throws \RuntimeException
     */
    public function getDb(SchemaConfigInterface $schema): DbInterface;
}
