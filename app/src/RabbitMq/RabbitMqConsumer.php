<?php

declare(strict_types=1);

namespace App\RabbitMq;

use App\Entity\FailedMessage;
use App\Repository\FailedMessageRepository;
use Closure;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

final class RabbitMqConsumer implements Consumer
{
    private int $errors;
    private bool $ack;
    private bool $stop = false;

    public function __construct(
        private Connection $connection,
        private FailedMessageRepository $failedMessageRepository
    ) {
    }

    public function consume(string $queue, int $errors, bool $ack): void
    {
        $this->errors = $errors;
        $this->ack = $ack;

        $channel = $this->connection->getChannel();

        $channel->basic_consume(
			$queue, '', false, false, false, false, $this->getCallback()
        );

        $loopRetries = 50;
        while ($channel->is_consuming() && $loopRetries > 0 && !$this->stop){
            dump("Contador consumo: $loopRetries");
            try {
                $channel->wait(null, false, 6);
            } catch (AMQPTimeoutException $exception) {

            } finally {
                --$loopRetries;
            }
        }

        $this->connection->close();
    }

    private function getCallback(): Closure
    {
        return function (AMQPMessage $message) {
            try {
                if ($this->errors > 0) {
                    throw new \Exception('Error');
                }

                dump("Mensaje: $message->body");


	            if ($message->body === 'stopConsumer') {
		            $this->stop = true;
	            }
            } catch (\Throwable $exception) {
                --$this->errors;

                /** @var AMQPTable $headers */
                $headers = $message->get('application_headers');
                $retries = $headers->getNativeData()['x-retries'] ?? 0;
                dump("Contador intento fallido: $retries");

                if ($retries < 3) {
                    $headers->set('x-retries', $retries+1);
                    $this->connection->publish($message, 'retry_exchange', 'retry_queue');
                } else {
                    $failedMessage = new FailedMessage();
                    $failedMessage->setMessage($message->body);
                    $failedMessage->setQueue($message->getRoutingKey());
                    $failedMessage->setError($exception->getMessage());

                    $this->failedMessageRepository->save($failedMessage);
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
