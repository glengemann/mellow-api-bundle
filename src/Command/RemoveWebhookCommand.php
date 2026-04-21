<?php

declare(strict_types=1);

namespace MellowApiBundle\Command;

use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(
    name: 'mellow:webhook:delete',
    description: 'Remove webhook from Mellow',
)]
class RemoveWebhookCommand extends Command
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->clientFactory->create();

        $apiToken = '';
        $client->authenticate($apiToken);

        $response = $client->weebhook()->remove();

        return Command::SUCCESS;
    }
}
