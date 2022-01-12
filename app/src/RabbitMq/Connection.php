<?php

declare(strict_types=1);

namespace App\RabbitMq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

interface Connection
{
    public function getChannel(): AMQPChannel;
    public function publish(AMQPMessage $message, string $exchange, string $routingKey): void;
    public function close(): void;
}
