<?php

namespace GlebecV\Model;

use Bestnetwork\Telnet\TelnetClient;
use Bestnetwork\Telnet\TelnetException;
use GlebecV\DTO\StationCollection;


class UhpHubPinger
{
    private const PACKET_SIZE = 40;
    private const UHP_PROMPT  = '#';
    private const UHP_TRAILING_COMMAND = '';

    private $telnetClient;

    /**
     * UhpHubPinger constructor.
     * @param string $address
     * @throws HubTelnetConnectException
     */
    public function __construct(string $address)
    {
        $this->uhpPrompt = '(SAVE)(NMS)29#'; //chr(0x23).chr(0x20);
        try {
            $this->telnetClient = new TelnetClient($address, 23, 10, '#');
        } catch (TelnetException $exception) {
            throw new HubTelnetConnectException('Unable to connect '.$address.PHP_EOL, 1);
        }
    }

    /**
     * @param StationCollection $item
     * @param int $count
     * @param int $timeout
     * @param int $attempts
     * @return array
     * @throws HubTelnetConnectException
     */
    public function ping(StationCollection $item, int $count, int $timeout, int $attempts): array
    {
        $size = self::PACKET_SIZE;
        $pingCommand = "ping {$item->ip} {$count} {$size} {$timeout} {$item->vlan}";

        $counter = 0;
        do {
            $counter++;
            try {
                $this->telnetClient->execute($pingCommand, self::UHP_PROMPT);
                $response = $this->telnetClient->execute(self::UHP_TRAILING_COMMAND, self::UHP_PROMPT);
            } catch (TelnetException $exception) {
                throw new HubTelnetConnectException('Unable to ping '.$item->ip.PHP_EOL, 1);
            }
            $result = $this->parseResponse($response);
            if ($attempts === $counter) {
                break;
            }
        } while (!$result['ok']);

        return [
            'ok'       => $result['ok'],
            'res'      => $result['info'],
            'attempts' => $counter
        ];
    }

    private function parseResponse(string $data)
    {
        $arr = explode("\r\n", $data);
        if (2 < count($arr) && isset($arr[count($arr)-2])) {
            $transmitted = (int)explode(';', $arr[count($arr)-2])[0];
            $lost = (int)explode(';', $arr[count($arr)-2])[1];
            return [
                'ok'   => ($transmitted - $lost) !== 0,
                'info' => $arr[count($arr)-1],
            ];
        } else {
            throw new HubTelnetConnectException('Connection lost', 1);
        }
    }
}