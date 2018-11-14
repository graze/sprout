<?php

namespace Graze\Sprout\Db\Parser;

use Graze\Sprout\Db\Table;

class TableFilterer
{
    /**
     * @param Table[]  $tables   list of tables to filter
     * @param string[] $excludes List of regular expressions to filter tables based on
     *
     * @return Table[] List of table with the excludes removed
     */
    public function filter(array $tables, array $excludes): array
    {
        return array_values(array_filter(
            $tables,
            function (Table $table) use ($excludes) {
                foreach ($excludes as $regex) {
                    $regex = mb_substr($regex, 0, 1) === '/' ? $regex : sprintf('/^%s$/', $regex);
                    if (preg_match($regex, $table->getName())) {
                        return false;
                    }
                }
                return true;
            }
        ));
    }
}
