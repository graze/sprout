<?php

namespace Graze\Sprout\File\Reader;

use Graze\Sprout\Db\Table;
use Graze\Sprout\SeedDataInterface;

interface ReaderInterface
{
    /**
     * Parse a file and generate seed data from it
     *
     * @param Table $table
     *
     * @return SeedDataInterface
     */
    public function parse(Table $table): SeedDataInterface;
}
