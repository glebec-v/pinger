<?php

namespace GlebecV\Repository;

use GlebecV\RepositoryInterface;
use GlebecV\UpStationRepository;

class Factory
{
    public static function fromDb(array $connectionInfo = []): UpStationRepository
    {
        return new NmsDbRepository();
    }

    /**
     * @param string $csvFile
     * file format:
     * 1 col(string): valid ip-address
     * [2 col(string): name]
     * ...
     * [N col(mixed|!resource): other_field
     * @return RepositoryInterface
     */
    public static function fromFile(string $csvFile): RepositoryInterface
    {
        // todo
    }

    /**
     * @param array $data
     * data format:
     * [
     *     [
     *         'ip' => 'valid ip-address',
     *         // ... optional
     *         'name' => 'string_name',
     *         // ... other sensible fields
     *     ]
     * ]
     *
     * @return RepositoryInterface
     */
    public static function fromArray(array $data = []): RepositoryInterface
    {
        if (0 === count($data)) {
            return new SimpleDemoRepo();
        }
    }

}