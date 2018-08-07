<?php

namespace GlebecV;

class Pinger
{
    private const TIMEOUT = 2;
    private const COUNT_ATTEMPTS = 1;

    public function ping(string $host)
    {
        exec(sprintf('ping -c %d -W %d %s', self::COUNT_ATTEMPTS, self::TIMEOUT, escapeshellarg($host)), $res, $rval);
        return $rval === 0;
    }
}