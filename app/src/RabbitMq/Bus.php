<?php

declare(strict_types=1);

namespace App\RabbitMq;

interface Bus
{
    public function publish(string $message, string $routingKey): void;
}
