<?php

namespace Graze\Sprout\Test\Unit\Parser;

use Graze\Sprout\Parser\TableFilterer;
use Graze\Sprout\Test\TestCase;

class TableFilterableTest extends TestCase
{
    /**
     * @dataProvider filterData
     *
     * @param string[] $tables
     * @param string[] $excludes
     * @param string[] $expected
     */
    public function testFilter(array $tables, array $excludes, array $expected)
    {
        $tableFilterer = new TableFilterer();

        $output = $tableFilterer->filter($tables, $excludes);

        $this->assertEquals($expected, $output);
    }

    /**
     * @return array
     */
    public function filterData()
    {
        return [
            [ // test standard string matching
              ['table1', 'table2', 'table3'],
              ['table1'],
              ['table2', 'table3'],
            ],
            [ // test simple regex
              ['table1', 'table2', 'table3'],
              ['table[23]'],
              ['table1'],
            ],
            [ // test multiple excludes
              ['table1', 'table2', 'table3'],
              ['table2', 'table3'],
              ['table1'],
            ],
            [ // test multiple regex
              ['table1', 'table2', 'table11', 'table12'],
              ['table\d', 'table[1]{2}'],
              ['table12'],
            ],
            [ // test complex regex
              ['table1', 'Table2', 'table11', 'table1s'],
              ['/^table\d{1}(?!\d)/i'],
              ['table11'],
            ],
        ];
    }
}
