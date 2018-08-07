<?php

namespace GlebecV;

use GlebecV\DTO\StationCollection;
use Monolog\Logger;

class Pinger
{
    private const TIMEOUT = 2;
    private const COUNT = 1;
    private const COUNT_ATTEMTS = 3;

    private $ipCollection;
    private $logger;

    /**
     * Pinger constructor.
     * @param RepositoryInterface $ipCollection
     * @param Logger $logger
     */
    public function __construct(RepositoryInterface $ipCollection, Logger $logger)
    {
        $this->ipCollection = $ipCollection;
        $this->logger       = $logger;
    }

    /**
     *
     */
    public function executePings()
    {
        $collection = $this->ipCollection->getIpCollection();
        foreach ($collection as $counter => $item) {
            /** @var StationCollection $item */
            $result = $this->ping($item->ip);
            if ($result['ok']) {
                $this->logger->info((string)$counter, [$item->toArray(), $result['res'], 'attempts' => $result['attempts']]);
            } else {
                $this->logger->error((string)$counter, [$item->toArray(), 'attempts' => $result['attempts']]);
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