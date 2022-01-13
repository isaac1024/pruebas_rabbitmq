<?php

namespace App\Command;

use App\RabbitMq\Bus;
use App\Repository\FailedMessageRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:consume-failed-message',
    description: 'Add a short description for your command',
)]
class ConsumeFailedMessageCommand extends Command
{
    public function __construct(
		private Bus $bus,
		private FailedMessageRepository $failedMessageRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');

        $failedMessage = $this->failedMessageRepository->find($id);
        if (!$failedMessage) {
            return Command::INVALID;
        }

        $this->bus->publish($failedMessage->getMessage(), $failedMessage->getQueue());
        $this->failedMessageRepository->delete($failedMessage);

        return Command::SUCCESS;
    }
}
