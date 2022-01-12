<?php

declare(strict_types=1);

namespace App\RabbitMq;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

final class RabbitMqBus implements Bus
{
    public function __construct(private Connection $connection)
    {
    }

    public function publish(string $message, string $routingKey): void
    {
        $headers = new AMQPTable();
        $headers->set('x-retries', 0);

        $message = new AMQPMessage($message);
        $message->set('application_headers', $headers);

        $this->connection->publish($message, "", $routingKey);

        $this->connection->close();
    }
}
