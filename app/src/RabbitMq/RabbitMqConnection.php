<?php

declare(strict_types=1);

namespace App\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class RabbitMqConnection implements Connection
{
    private ?AMQPStreamConnection $connection = null;
    private ?AMQPChannel $channel = null;

    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password
    ){
    }

    public function getChannel(): AMQPChannel
    {
        if (!$this->channel || !$this->channel->is_open()) {
            $this->connect();
        }

        return $this->channel;
    }

    public function publish(
		AMQPMessage $message,
		string $exchange,
		string $routingKey
    ): void {
        $this->getChannel()->basic_publish($message, $exchange, $routingKey);
    }

    private function connect(): void
    {
        if (!$this->connection) {
            $this->connection = new AMQPStreamConnection(
				$this->host, $this->port, $this->user, $this->password
            );
        }

        $this->channel = $this->connection->channel();
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }
}
