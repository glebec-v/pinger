<?php

namespace GlebecV;

interface UpStationRepository extends RepositoryInterface
{
    public function isUp(string $serialNumber): bool;
}