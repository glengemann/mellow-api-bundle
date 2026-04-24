<?php

declare(strict_types=1);

namespace MellowApiBundle\Command;

use Mellow\Api\Webhook\Parameter\CreateParameters;
use Mellow\Api\Webhook\Parameter\Status;
use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(
    name: 'mellow:webhook:create',
    description: 'Create webhook from Mellow',
)]
class CreateWebhookCommand extends Command
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly string $webhookUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->clientFactory->create();

        $apiToken = '';
        $client->authenticate($apiToken);

        $parameters = (new CreateParameters())
            ->status(Status::ENABLED)
            ->url($this->webhookUrl);

        $response = $client->webhook()->create($parameters);

        return Command::SUCCESS;
    }
}
