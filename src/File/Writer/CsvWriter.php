<?php

namespace Graze\Sprout\File\Writer;

use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\SeedDataInterface;
use League\Csv\Writer;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

class CsvWriter implements WriterInterface
{
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * CsvWriter constructor.
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
     * @param SeedDataInterface $data
     * @param Schema            $schema
     * @param Table             $table
     *
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function write(SeedDataInterface $data, Schema $schema, Table $table)
    {
        $file = sprintf('%s/%s.csv', $schema->getPath(), $data->getTableName());
        $stream = fopen('php://tmp', 'w+');
        $writer = Writer::createFromStream($stream);

        $rows = $data->getData();
        $fields = array_keys(reset($rows));

        $writer->insertOne($fields);
        $writer->insertAll($rows);

        if ($this->filesystem->writeStream($file, $stream, new Config()) === false) {
            throw new \RuntimeException("csv writer: failed to write data to file: {$file}");
        }
        if ($table->getPath() && realpath($table->getPath()) != realpath($file)) {
            $this->filesystem->delete($table->getPath());
        }
    }
}
