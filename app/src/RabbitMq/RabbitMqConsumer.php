<?php

declare(strict_types=1);

namespace App\RabbitMq;

use App\Entity\FailedMessage;
use Closure;
use Doctrine\Persistence\ManagerRegistry;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

final class RabbitMqConsumer implements Consumer
{
    private int $errors;
    private bool $ack;

    public function __construct(
        private Connection $connection,
        private ManagerRegistry $doctrine
    ) {
    }

    public function consume(string $queue, int $errors, bool $ack): void
    {
        $this->errors = $errors;
        $this->ack = $ack;

        $channel = $this->connection->getChannel();

        $channel->basic_consume($queue, '', false, false, false, false, $this->getCallback());

        while ($channel->is_open()){
            $channel->wait();
        }
    }

    private function getCallback(): Closure
    {
        return function (AMQPMessage $message) {
            try {
                if ($this->errors > 0) {
                    throw new \Exception('Error');
                }

                dump($message->body);
            } catch (\Throwable $exception) {
                --$this->errors;

                /** @var AMQPTable $headers */
                $headers = $message->get('application_headers');
                $retries = $headers->getNativeData()['x-retries'] ?? 0;
                dump($retries);

                if ($retries < 3) {
                    $headers->set('x-retries', $retries+1);
                    $this->connection->publish($message, 'retry_exchange', 'retry_queue');
                } else {
                    $em = $this->doctrine->getManager();

                    $failedMessage = new FailedMessage();
                    $failedMessage->setMessage($message->body);
                    $failedMessage->setQueue($message->getRoutingKey());
                    $failedMessage->setError($exception->getMessage());

                    $em->persist($failedMessage);
                    $em->flush();
                }
            } finally {
                if ($this->ack) {
                    $message->ack();
                } else {
                    dump("Not send ack");
                }
            }
        };
    }
}
