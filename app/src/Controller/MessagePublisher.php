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
    public function __invoke(): JsonResponse
    {
        $this->bus->publish('{"message": "Ok"}', 'work_queue');

        return new JsonResponse();
    }
}
