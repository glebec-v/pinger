<?php

namespace GlebecV;

use GlebecV\DTO\StationCollection;
use Monolog\Logger;

class Pinger
{
    private const TIMEOUT = 2;
    private const COUNT = 1;
    private const COUNT_ATTEMTS = 3;

    private $ipRepository;
    private $logger;

    /**
     * Pinger constructor.
     * @param RepositoryInterface $ipRepository
     * @param Logger $logger
     */
    public function __construct(RepositoryInterface $ipRepository, Logger $logger)
    {
        $this->ipRepository = $ipRepository;
        $this->logger       = $logger;
    }

    /**
     * ping operation for every station
     * executes constantly
     */
    public function permanentExecutePings()
    {
        if ($this->ipRepository instanceof UpStationRepository) {
            while(true) {
                $collection = $this->ipRepository->getIpCollection();
                foreach ($collection as $counter => $item) {
                    /** @var StationCollection $item */
                    if ($this->ipRepository->isUp($item->serial)) {
                        $result = $this->ping($item->ip);
                        if ($result['ok']) {
                            $this->logger->info((string)$counter, ['target' => $item->toArray(), 'result' => $result['res'], 'attempts' => $result['attempts']]);
                        } else {
                            $this->logger->error((string)$counter, ['target' => $item->toArray(), 'attempts' => $result['attempts']]);
                        }
                    } else {
                        $this->logger->warning((string)$counter.' DOWN', ['target' => $item->toArray()]);
                    }
                }
            }
        }
    }

    /**
     * common ping operation, executes once
     */
    public function executePings()
    {
        $collection = $this->ipRepository->getIpCollection();
        foreach ($collection as $counter => $item) {
            /** @var StationCollection $item */
            $result = $this->ping($item->ip);
            if ($result['ok']) {
                $this->logger->info((string)$counter, ['target' => $item->toArray(), 'result' => $result['res'], 'attempts' => $result['attempts']]);
            } else {
                $this->logger->error((string)$counter, ['target' => $item->toArray(), 'attempts' => $result['attempts']]);
            }
        }
    }

    /**
     * @param string $host
     * @return array
     */
    private function ping(string $host)
    {
        $counter = 0;
        do {
            $counter++;
            exec(sprintf('ping -c %d -W %d %s', self::COUNT, self::TIMEOUT, escapeshellarg($host)), $res, $rval);
            if (self::COUNT_ATTEMTS === $counter) {
                break;
            }
        } while (0 !== $rval);

        return [
            'ok'       => $rval === 0,
            'res'      => !empty($res[1] ?? '') ? $res[1] : '',
            'attempts' => $counter
        ];
    }
}