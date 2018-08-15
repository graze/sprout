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

namespace Graze\Sprout\Test\Unit\Parser;

use Graze\Sprout\Config;
use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Parser\FileTablePopulator;
use Graze\Sprout\Parser\ParsedSchema;
use Graze\Sprout\Parser\TableFilterer;
use Graze\Sprout\Test\TestCase;
use League\Flysystem\AdapterInterface;
use Mockery;

class FileTablePopulatorTest extends TestCase
{
    /** @var FileTablePopulator */
    private $tablePopulator;
    /** @var mixed */
    private $fileSystem;
    /** @var mixed */
    private $tableFilterer;

    public function setUp()
    {
        $this->fileSystem = Mockery::mock(AdapterInterface::class);
        $this->tableFilterer = Mockery::mock(TableFilterer::class);
        $this->tablePopulator = new FileTablePopulator($this->fileSystem, $this->tableFilterer);
    }

    public function testPopulateTablesWithExistingTablesDoesNothing()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', ['table1', 'table2']);
        $config->allows(['getExcludes' => []]);

        $output = $this->tablePopulator->populateTables($parsedSchema);

        $this->assertSame($parsedSchema, $output);

        $this->assertEquals(['table1', 'table2'], $parsedSchema->getTables());
    }

    public function testPopulateTablesWithNoTablesWillSearchTheFilesystemForTables()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', []);

        $this->fileSystem->allows()
                         ->has('/a/path')
                         ->andReturns(true);

        $file1 = ['path' => '/a/path/table1.sql', 'size' => 1234];
        $file2 = ['path' => '/a/path/table2.sql', 'size' => 1234];

        $this->fileSystem->allows()
                         ->listContents('/a/path')
                         ->andReturns([$file1, $file2]);

        $config->allows(['getExcludes' => []]);

        $output = $this->tablePopulator->populateTables($parsedSchema);

        $this->assertSame($parsedSchema, $output);
        $this->assertEquals(['table1', 'table2'], $output->getTables());
    }

    public function testPopulateTablesWillFilterOutExcludedTables()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', []);

        $this->fileSystem->allows()
                         ->has('/a/path')
                         ->andReturns(true);

        $file1 = ['path' => '/a/path/table1.sql', 'size' => 1234];
        $file2 = ['path' => '/a/path/table2.sql', 'size' => 1234];

        $this->fileSystem->allows()
                         ->listContents('/a/path')
                         ->andReturns([$file1, $file2]);

        $config->allows(['getExcludes' => ['table1']]);

        $this->tableFilterer->allows()
                            ->filter(['table1', 'table2'], ['table1'])
                            ->andReturns(['table2']);

        $output = $this->tablePopulator->populateTables($parsedSchema);

        $this->assertSame($parsedSchema, $output);
        $this->assertEquals(['table2'], $output->getTables());
    }

    public function testPopulateTablesWhenFolderDoesNotExistReturnsNull()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', []);
        $config->allows(['getExcludes' => []]);

        $this->fileSystem->allows()
                         ->has('/a/path')
                         ->andReturns(false);

        $output = $this->tablePopulator->populateTables($parsedSchema);

        $this->assertNull($output);
    }

    public function testPopulateTablesWithNoFilesReturnsNull()
    {
        $config = Mockery::mock(Config\SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '/a/path', []);
        $config->allows(['getExcludes' => []]);

        $this->fileSystem->allows()
                         ->has('/a/path')
                         ->andReturns(true);

        $this->fileSystem->allows()
                         ->listContents('/a/path')
                         ->andReturns([]);

        $output = $this->tablePopulator->populateTables($parsedSchema);

        $this->assertNull($output);
    }

    public function testEmptyPathReturnsNull()
    {
        $config = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new ParsedSchema($config, '', []);

        $output = $this->tablePopulator->populateTables($parsedSchema);

        $this->assertNull($output);
    }
}
