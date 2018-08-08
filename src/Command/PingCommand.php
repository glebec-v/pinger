<?php

namespace GlebecV\Command;


use GlebecV\Pinger;
use GlebecV\Repository\SimpleDemoRepo;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PingCommand extends Command
{
    /**
     * command configuration
     * up to https://symfony.com/doc/current/console.html
     */
    protected function configure()
    {
        $this
            ->setName('ping')
            ->setDescription('Pings stuff')
            ->setHelp(
                'This command pings ip-addresses provided in file or other source (database)'
                .PHP_EOL
                .'with default parameters that can be changed by user')
            ->addOption(
                'timeout',
                't',
                InputOption::VALUE_REQUIRED,
                'Time to wait for a response, in seconds. The option affects only timeout in absence of  any  responses,  otherwise
              ping waits for two RTTs',
                2
            )
            ->addOption(
                'count',
                'c',
                InputOption::VALUE_REQUIRED,
                'Stop after sending count ECHO_REQUEST packets',
                1
            )
            ->addOption(
                'attempts',
                'a',
                InputOption::VALUE_REQUIRED,
                'Number of attempts execute GNU ping if first attempt was unsuccessful, including first attempt',
                3
            )
            ->addOption(
                'logfile',
                'f',
                InputOption::VALUE_REQUIRED,
                'full path to logfile',
                'ping.log'
            )
            ->addOption(
                'permanent',
                null,
                InputOption::VALUE_NONE,
                'permanent executing pings with provided set of ip-addresses, to stop use ctrl-C'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pinger = $this->createPinger($input);
        $permanent = $input->getOption('permanent');

        if ($permanent) {
            $pinger->permanentExecutePings();
        } else {
            $pinger->executePings();
        }
    }

    private function createPinger(InputInterface $input): Pinger
    {
        $repo = new SimpleDemoRepo();

        $logFile = $input->getOption('logfile');
        $logger = $logger = new Logger('ping');
        $logger->pushHandler(new StreamHandler($logFile, Logger::INFO));

        return new Pinger($repo, $logger);
    }
}