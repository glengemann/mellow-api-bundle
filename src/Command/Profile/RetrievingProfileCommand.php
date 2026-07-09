<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Profile;

use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mellow:profile:retrieve',
    description: 'Retrieves the profile of the authenticated user',
)]
class RetrievingProfileCommand extends Command
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = $this->clientFactory->create();

        $response = $client->profile()->profile();

        $io->title('Profile');
        $rows = [];

        foreach (get_object_vars($response) as $field => $value) {
            $rows[] = [$field, $this->formatValue($value)];
        }

        $io->table(['Field', 'Value'], $rows);

        return Command::SUCCESS;
    }

    private function formatValue(mixed $value): string
    {
        if (null === $value) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return false === $encoded ? '[unserializable array]' : $encoded;
        }

        return (string) $value;
    }
}
