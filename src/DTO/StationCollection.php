<?php

namespace GlebecV\DTO;

use GlebecV\ItemInterface;

class StationCollection implements ItemInterface
{
    public $ip;
    public $serial;
    public $name;
    public $vlan;

    /**
     * StationCollection constructor.
     * @param string $ip
     * @param string $serial
     * @param string $name
     * @param int    $vlan
     */
    public function __construct(string $ip, string $serial, string $name, int $vlan)
    {
        $this->ip = $ip;
        $this->serial = $serial;
        $this->name = $name;
        $this->vlan = $vlan;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ip'     => $this->ip,
            'serial' => $this->serial,
            'name'   => $this->name,
            'vlan'   => $this->vlan,
        ];
    }
}