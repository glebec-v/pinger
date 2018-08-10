<?php

namespace GlebecV\Repository;

use GlebecV\DTO\StationCollection;
use GlebecV\RepositoryInterface;

class SimpleDemoRepo implements RepositoryInterface
{
    public function getIpCollection(): array
    {
        return [
            new StationCollection('10.0.2.40', '24857', 'station-1000-40', 12),
            new StationCollection('10.0.2.49', '40090', 'station-100-49', 12),
            new StationCollection('10.0.2.39', '24856', 'station-1000-39', 12),
            new StationCollection('10.0.0.10', '12345', 'none-station', 1),
        ];
    }
}