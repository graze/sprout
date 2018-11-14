<?php

namespace Graze\Sprout\File\Writer;

use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\SeedDataInterface;

interface WriterInterface
{
    /**
     * Parse a file and generate seed data from it
     *
     * @param SeedDataInterface $data
     * @param Schema            $schema
     * @param Table             $table
     *
     * @return void
     */
    public function write(SeedDataInterface $data, Schema $schema, Table $table);
}
