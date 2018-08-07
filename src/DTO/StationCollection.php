<?php

namespace GlebecV\DTO;

use GlebecV\ItemInterface;

class StationCollection implements ItemInterface
{
    public $ip;
    public $serial;
    public $name;

    public function __construct(string $ip, string $serial, string $name)
    {
        $this->ip = $ip;
        $this->serial = $serial;
        $this->name = $name;
    }

    public function toArray(): array
    {
        return [
            'ip'     => $this->ip,
            'serial' => $this->serial,
            'name'   => $this->name
        ];
    }
}