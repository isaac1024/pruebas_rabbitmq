<?php

declare(strict_types=1);

namespace App\RabbitMq;

interface Consumer
{
    public function consume(string $queue, int $errors, bool $noAck);
}
