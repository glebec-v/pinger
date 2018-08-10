<?php

namespace GlebecV\Repository;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use GlebecV\DTO\StationCollection;
use GlebecV\UpStationRepository;

class NmsDbRepository implements UpStationRepository
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
  Route.addr        AS ip,
  HubVlan.number    AS vlan,
  ((Remote.rx_only = 0) && ((RemoteState.faults&0x8000 <> 0) || (RemoteState.core_state > 0)))
    ||
  ((Remote.rx_only = 1) && (RemoteState.core_state = 6))
                    as down,
  RemoteState.faults&0xFFFF <> 0 as config,
  RemoteState.core_state,
  RemoteState.updated,
  UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(updated) > 900 as outdated,
  Remote.rx_only,
  Remote.enabled
FROM Remote
  LEFT JOIN Route ON Remote.id = Route.remote_id
  LEFT JOIN HubVlan ON Route.hub_vlan = HubVlan.id
  LEFT OUTER JOIN RemoteState ON Remote.id = RemoteState.remote_id
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

            if (!$this->analyzeFaults($item)) {
                continue;
            }

            $ret[] = new StationCollection(
                $item['ip'],
                $item['serial'],
                $item['name'],
                (int)$item['vlan']
            );
        }

        return $ret;
    }

    /**
     * @param string $serialNumber
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    public function isUp(string $serialNumber): bool
    {
        $sql = <<<IS_UP
SELECT
  ((Remote.rx_only = 0) && ((RemoteState.faults&0x8000 <> 0) || (RemoteState.core_state > 0)))
  ||
  ((Remote.rx_only = 1) && (RemoteState.core_state = 6))
                                as down,
  RemoteState.faults&0xFFFF <> 0 as config,
  RemoteState.core_state,
  RemoteState.updated,
  UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(updated) > 900 as outdated,
  Remote.rx_only,
  Remote.enabled
FROM Remote
  LEFT OUTER JOIN RemoteState ON Remote.id = RemoteState.remote_id
WHERE Remote.serial_num = {$serialNumber};
IS_UP;

        $result = $this->connection->query($sql)->fetchAll();
        if (0 !== count($result) && isset($result[0])) {
            return $this->analyzeFaults($result[0]);
        }
        return false;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function analyzeFaults(array $data)
    {
        if (0 === (int)$data['enabled']) {
            return false;
        }

        if (is_null($data['down']) || 1 === (int)$data['down']) {
            return false;
        }

        if (0 === (int)$data['down'] && 1 === (int)$data['config']) {
            return true;
        }

        if (1 === (int)$data['rx_only']) {
            return true;
        }

        if (3 === (int)$data['core_state']) {
            // Controlled Unavailable
            return false;
        }

        if (is_null($data['updated'])) {
            return false;
        }

        if (0 !== (int)$data['outdated']) {
            return false;
        }

        return true;
    }
}