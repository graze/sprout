<?php

namespace Graze\Sprout\File\Writer;

use Graze\Sprout\Db\Schema;
use Graze\Sprout\Db\Table;
use Graze\Sprout\SeedDataInterface;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Symfony\Component\Yaml\Yaml;

class YamlWriter implements WriterInterface
{
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * YamlWriter constructor.
     *
     * @param AdapterInterface $filesystem
     */
    public function __construct(AdapterInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param SeedDataInterface $data
     * @param Schema            $schema
     * @param Table             $table
     */
    public function write(SeedDataInterface $data, Schema $schema, Table $table)
    {
        $file = sprintf('%s/%s.yml', $schema->getPath(), $data->getTableName());

        $write = $this->filesystem->write(
            $file,
            Yaml::dump([
                'table'     => $data->getTableName(),
                'seed_type' => $data->getSeedType(),
                'data'      => $data->getData(),
            ]),
            new Config()
        );
        if ($write === false) {
            throw new \RuntimeException("yaml writer: failed to write data to file: {$file}");
        }
        // delete the old file only if the write was successful
        if ($table->getPath() && realpath($table->getPath()) != realpath($file)) {
            $this->filesystem->delete($table->getPath());
        }
    }
}
