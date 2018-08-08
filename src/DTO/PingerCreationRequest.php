<?php

namespace GlebecV\DTO;


use GlebecV\RepositoryInterface;
use Monolog\Logger;

class PingerCreationRequest
{
    public $logger;
    public $repository;
    public $timeout;
    public $count;
    public $attempts;

    public function __construct(
        Logger $logger,
        RepositoryInterface $repository,
        int $timeout,
        int $count,
        int $attempts
    )
    {
        $this->logger     = $logger;
        $this->repository = $repository;
        $this->timeout    = $timeout;
        $this->count      = $count;
        $this->attempts   = $attempts;
    }

}