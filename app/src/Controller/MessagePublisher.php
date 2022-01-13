<?php

declare(strict_types=1);

namespace App\Controller;

use App\RabbitMq\Bus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class MessagePublisher
{
    public function __construct(private Bus $bus)
    {
    }

    #[Route('/')]
    public function message(): JsonResponse
    {
        $this->bus->publish('{"message": "Ok"}', 'work_queue');

        return new JsonResponse();
    }

    #[Route('/stop')]
    public function stop(): JsonResponse
    {
        $this->bus->publish('stopConsumer', 'work_queue');

        return new JsonResponse();
    }
}
