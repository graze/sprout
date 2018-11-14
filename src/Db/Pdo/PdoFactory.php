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

namespace Graze\Sprout\Db\Pdo;

use Graze\Sprout\Config\ConnectionConfigInterface;
use Graze\Sprout\Db\DbFactoryInterface;
use PDO;

class PdoFactory
{
    /**
     * @param ConnectionConfigInterface $connection
     * @param array                     $options
     *
     * @return PDO
     */
    public function getPdo(ConnectionConfigInterface $connection, array $options = [])
    {
        return new PDO(
            $connection->getDsn(),
            $connection->getUser(),
            $connection->getPassword(),
            $options
        );
    }
}
