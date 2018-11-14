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

use Graze\Sprout\Config\SchemaConfigInterface;
use Graze\Sprout\Db\Schema;
use Graze\Sprout\Test\TestCase;
use Mockery;

class ParsedSchemaTest extends TestCase
{
    public function testConstructor()
    {
        $configuration = Mockery::mock(SchemaConfigInterface::class);
        $configuration->allows()
                      ->getSchema()
                      ->andReturns('the_schema');
        $parsedSchema = new Schema($configuration, 'path', ['table1', 'table2']);

        $this->assertSame($configuration, $parsedSchema->getSchemaConfig());
        $this->assertEquals('the_schema', $parsedSchema->getSchemaName());
        $this->assertEquals('path', $parsedSchema->getPath());
        $this->assertEquals(['table1', 'table2'], $parsedSchema->getTables());
    }

    public function testTheTablesCanBeChanged()
    {
        $configuration = Mockery::mock(SchemaConfigInterface::class);
        $parsedSchema = new Schema($configuration, 'path', []);

        $this->assertSame($configuration, $parsedSchema->getSchemaConfig());
        $this->assertEquals([], $parsedSchema->getTables());

        $this->assertSame($parsedSchema, $parsedSchema->setTables(['table1', 'table2', 'table3']));
        $this->assertEquals(['table1', 'table2', 'table3'], $parsedSchema->getTables());
    }
}
