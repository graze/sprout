<?php

namespace Graze\Sprout\File\Writer;

use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\SeedDataInterface;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

class JsonWriter implements WriterInterface
{
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * JsonWriter constructor.
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
     */
    public function write(SeedDataInterface $data, Schema $schema, Table $table)
    {
        $file = sprintf('%s/%s.json', $schema->getPath(), $data->getTableName());

        $write = $this->filesystem->write(
            $file,
            json_encode([
                'table'     => $data->getTableName(),
                'seed_type' => $data->getSeedType(),
                'data'      => $data->getData(),
            ]),
            new Config()
        );
        if ($write === false) {
            throw new \RuntimeException("json writer: failed to write data to file: {$file}");
        }
        if ($table->getPath() && realpath($table->getPath()) != realpath($file)) {
            $this->filesystem->delete($table->getPath());
        }
    }
}
