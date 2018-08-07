<?php

namespace GlebecV\Repository;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use GlebecV\DTO\StationCollection;
use GlebecV\RepositoryInterface;

class NmsDbRepository implements RepositoryInterface
{
    private $connection;

    /**
     * NmsDbRepository constructor.
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct()
    {
        $connectionParams = array(
            'dbname' => 'nms_db',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );

        $this->connection = DriverManager::getConnection($connectionParams, new Configuration());
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getIpCollection(): array
    {
        $sql = <<<IP_ADDRESSES
SELECT
  Remote.serial_num AS serial,
  Remote.name       AS name,
  Route.addr        AS ip
FROM Remote
LEFT JOIN Route ON Remote.id = Route.remote_id
WHERE
    Remote.enabled = 1
AND Route.type = 3
AND
    CONVERT(
      SUBSTRING(
        Route.data FROM INSTR(Route.data, '"private":"')
                          + LENGTH('"private":"') FOR LOCATE('"', Route.data, INSTR(Route.data, '"private":"') + LENGTH('"private":"'))
                          - INSTR(Route.data, '"private":"')
                          - LENGTH('"private":"')
          )
      , UNSIGNED INTEGER) = 0;
IP_ADDRESSES;

        $ret = [];
        $result = $this->connection->query($sql)->fetchAll();

        foreach ($result as $item) {
            if (is_null($item['ip'])) {
                continue;
            }
            $ret[] = new StationCollection(
                $item['ip'],
                $item['serial'],
                $item['name']
            );
        }

        return $ret;
    }
}