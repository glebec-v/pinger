<?php

namespace GlebecV\Repository;

use GlebecV\DTO\StationCollection;
use GlebecV\RepositoryInterface;

class SimpleDemoRepo implements RepositoryInterface
{
    public function getIpCollection()
    {
        return [
            new StationCollection('10.0.2.40', '24857', 'station-1000-40'),
            new StationCollection('10.0.2.49', '40090', 'station-100-49'),
            new StationCollection('10.0.2.39', '24856', 'station-1000-39'),
            new StationCollection('10.0.0.10', '12345', 'none-station'),
        ];
    }
}