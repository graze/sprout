<?php

namespace Graze\Sprout\File\Reader;

use Graze\Sprout\Db\Table;
use Graze\Sprout\SeedData;
use Graze\Sprout\SeedDataInterface;
use League\Flysystem\AdapterInterface;
use Symfony\Component\Yaml\Yaml;

class YamlReader implements ReaderInterface
{
    /** @var AdapterInterface */
    private $filesystem;

    /**
     * YamlReader constructor.
     *
     * @param AdapterInterface $filesystem
     */
    public function __construct(AdapterInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Parse a file and generate seed data from it.
     *
     * This supports 2 different file formats:
     *
     * ### Pure Array
     *
     * ```yaml
     * - field: value
     *   field2: value2
     *   field3: value3
     *
     * - field: value4
     *   field2: value5
     *   field3: value6
     * ```
     *
     * ### With metadata
     *
     * ```yaml
     * table: name_of_table
     * seed_type: truncate
     *
     * data:
     * - field: value
     *   field2: value2
     *   field3: value3
     *
     * - field: value4
     *   field2: value5
     *   field3: value6
     * ```
     *
     * @param Table $table
     *
     * @return SeedDataInterface
     */
    public function parse(Table $table): SeedDataInterface
    {
        $read = $this->filesystem->read($table->getPath());
        if ($read === false) {
            throw new \RuntimeException("json parse: failed to read the contents of file: {$table->getPath()}");
        }
        $data = Yaml::parse($read['contents'], Yaml::PARSE_OBJECT);

        if (is_array($data)) {
            return new SeedData($table->getName(), $data);
        } elseif (isset($data->table) && isset($data->data) && is_array($data->data)) {
            return new SeedData($data->table, $data->data, $data->{'seed_type'} ?? SeedData::SEED_TYPE_TRUNCATE);
        } else {
            throw new \InvalidArgumentException("yaml parse: file `{$table->getPath()}` should be an array of data, or have the properties: `table` and `data`");
        }
    }
}
