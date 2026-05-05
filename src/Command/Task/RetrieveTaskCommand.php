<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Task;

use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(
    name: 'mellow:task:retrieve',
    description: 'Retrieve the details of a task from Mellow.',
)]
class RetrieveTaskCommand extends Command
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('uuid', InputArgument::REQUIRED, 'The UUID of the task to retrieve.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $uuid = $input->getArgument('uuid');

        $companyId = null;
        $client = $this->clientFactory->create($companyId);

        $response = $client->task()->retrieve($uuid);

        $output->writeln('Task Response:');
        $output->writeln('Currency: ' . $response->currency['currency']);
        $output->writeln('Currency ID: ' . $response->currency['id']);
        $output->writeln('State: ' . $response->state);

        return Command::SUCCESS;
    }
}
