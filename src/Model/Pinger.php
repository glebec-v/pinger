<?php

namespace GlebecV\Model;

use GlebecV\DTO\PingerCreationRequest;
use GlebecV\DTO\StationCollection;
use GlebecV\UpStationRepository;

class Pinger
{
    private $ipRepository;
    private $logger;
    private $timeout;
    private $count;
    private $attempts;

    /**
     * Model constructor.
     * @param PingerCreationRequest $request
     */
    public function __construct(PingerCreationRequest $request)
    {
        $this->ipRepository = $request->repository;
        $this->logger       = $request->logger;
        $this->timeout      = $request->timeout;
        $this->count        = $request->count;
        $this->attempts     = $request->attempts;
    }

    /**
     * ping executes constantly
     */
    public function permanentExecutePings()
    {
        while(true) {
            $collection = $this->ipRepository->getIpCollection();
            $this->executePings($collection);
        }
    }

    /**
     * ping executes once
     */
    public function executePingsOnce()
    {
        $collection = $this->ipRepository->getIpCollection();
        $this->executePings($collection);
    }

    /**
     * common ping operation, executes once
     */
    private function executePings($collection)
    {
        foreach ($collection as $counter => $item) {
            if ($collection instanceof UpStationRepository && !$collection->isUp($item->serial)) {
                $this->logger->warning((string)$counter.' DOWN', ['target' => $item->toArray()]);
                continue;
            }
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
            exec(sprintf('ping -c %d -W %d %s', $this->count, $this->timeout, escapeshellarg($host)), $res, $rval);
            if ($this->attempts === $counter) {
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