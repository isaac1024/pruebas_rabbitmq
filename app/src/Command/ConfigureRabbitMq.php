<?php

declare(strict_types=1);

namespace App\Command;

use App\RabbitMq\Connection;
use PhpAmqpLib\Wire\AMQPTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigureRabbitMq extends Command
{
    protected static $defaultName = 'app:configure-rabbitmq';

    public function __construct(private Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $channel = $this->connection->getChannel();

        $channel->exchange_declare('work_exchange', 'direct', false, true, false);
        $channel->queue_declare('work_queue', false, true, false, false);
        $channel->queue_bind('work_queue', 'work_exchange', 'work_queue');

        $amqpTable = new AMQPTable([
           'x-dead-letter-exchange' => 'work_exchange',
           'x-dead-letter-routing-key' => 'work_queue',
           'x-message-ttl' => 2500
       ]);
        $channel->exchange_declare('retry_exchange', 'direct', false, true, false);
        $channel->queue_declare('retry_queue', false, true, false, false, false, $amqpTable);
        $channel->queue_bind('retry_queue', 'retry_exchange', 'retry_queue');

        $this->connection->close();

        return Command::SUCCESS;
    }
}
