<?php

declare(strict_types=1);

namespace App\Command;

use App\RabbitMq\Consumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MessageConsumer extends Command
{
    protected static $defaultName = 'app:message-consumer';

    public function __construct(private Consumer $consumer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('errors', 'r', InputOption::VALUE_OPTIONAL, 'Num of errors', 0);
        $this->addOption('no-ack', 'a', InputOption::VALUE_NONE, 'Not send ack');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $errors = (int) $input->getOption('errors');
        $ack = !$input->getOption('no-ack');

        $this->consumer->consume('work_queue', $errors, $ack);

        return Command::SUCCESS;
    }
}
