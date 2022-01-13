<?php

declare(strict_types=1);

namespace App\RabbitMq;

use App\Entity\FailedMessage;
use App\Repository\FailedMessageRepository;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

final class RabbitMqBus implements Bus
{
    public function __construct(
        private Connection $connection,
        private FailedMessageRepository $failedMessageRepository
    ) {
    }

    public function publish(string $message, string $routingKey): void
    {
        try {
            $headers = new AMQPTable(['x-retries' => 0]);

            $message = new AMQPMessage($message);
            $message->set('application_headers', $headers);

            $this->connection->publish($message, "", $routingKey);
	        $this->connection->close();
        } catch (\Throwable $exception) {
            $failedMessage = new FailedMessage();
            $failedMessage->setMessage($message->body);
            $failedMessage->setQueue($message->getRoutingKey());
            $failedMessage->setError($exception->getMessage());

            $this->failedMessageRepository->save($failedMessage);
        }
    }
}
