<?php

namespace Graze\Sprout\File\Reader;

use Graze\Sprout\Db\Table;
use Graze\Sprout\SeedData;
use Graze\Sprout\SeedDataInterface;
use League\Csv\Reader;
use League\Flysystem\AdapterInterface;

class CsvReader implements ReaderInterface
{
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * CsvReader constructor.
     *
     * @param AdapterInterface $filesystem
     */
    public function __construct(AdapterInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Parse a file and generate seed data from it
     *
     * @param Table $table
     *
     * @return SeedDataInterface
     *
     * @throws \League\Csv\Exception
     */
    public function parse(Table $table): SeedDataInterface
    {
        $stream = $this->filesystem->readStream($table->getPath());
        if ($stream === false) {
            throw new \RuntimeException("parse csv: failed to open file: {$table->getPath()} for reading");
        }
        $csv = Reader::createFromStream($stream['stream']);
        $csv->setHeaderOffset(0);

        $data = iterator_to_array($csv);

        return new SeedData($table->getName(), $data);
    }
}
