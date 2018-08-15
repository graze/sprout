<?php

namespace Graze\Sprout\Parser;

class TableFilterer
{
    /**
     * @param string[] $tables   list of tables to filter
     * @param string[] $excludes List of regular expressions to filter tables based on
     *
     * @return string[] List of table with the excludes removed
     */
    public function filter(array $tables, array $excludes): array
    {
        return array_values(array_filter(
            $tables,
            function (string $table) use ($excludes) {
                foreach ($excludes as $regex) {
                    $regex = mb_substr($regex, 0, 1) === '/' ? $regex : sprintf('/^%s$/', $regex);
                    if (preg_match($regex, $table)) {
                        return false;
                    }
                }
                return true;
            }
        ));
    }
}
