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
    name: 'mellow:webhook:retrieve',
    description: 'Retrieve webhook from Mellow',
)]
class RetrieveWebhookCommand extends Command
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

        /**
         * @var array{
         *     url: string,
         *     status: 'enabled'|'disabled',
         *     secret: string,
         *     lastTriggered: ?string,
         *     lastError: ?string
         * } $response
         */
        $response = $client->webhook()->retrive();

        $output->writeln('Webhook details:');
        $output->writeln(sprintf('URL: %s', $response['url']));
        $output->writeln(sprintf('Status: %s', $response['status']));
        $output->writeln(sprintf('Secret: %s', $response['secret']));
        $output->writeln(sprintf('Last Triggered: %s', $response['lastTriggered'] ?? 'N/A'));
        $output->writeln(sprintf('Last Error: %s', $response['lastError'] ?? 'N/A'));

        return Command::SUCCESS;
    }
}
