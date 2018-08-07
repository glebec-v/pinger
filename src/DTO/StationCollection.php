<?php

namespace GlebecV\DTO;

use GlebecV\ItemInterface;

class StationCollection implements ItemInterface
{
    public $ip;
    public $serial;
    public $name;

    /**
     * StationCollection constructor.
     * @param string $ip
     * @param string $serial
     * @param string $name
     */
    public function __construct(string $ip, string $serial, string $name)
    {
        $this->ip = $ip;
        $this->serial = $serial;
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ip'     => $this->ip,
            'serial' => $this->serial,
            'name'   => $this->name
        ];
    }
}