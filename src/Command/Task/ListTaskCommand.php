<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Task;

use Mellow\Api\Task\Parameter\FilterParameters;
use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(
    name: 'mellow:task',
    description: 'List tasks from Mellow.',
)]
class ListTaskCommand extends Command
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->clientFactory->create();

        $parameters = (new FilterParameters());
        $response = $client->task()->list($parameters);

        if (true === empty($response)) {
            $output->writeln('<comment>No tasks found.</comment>');

            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['ID', 'UUID', 'Title', 'Price', 'Currency', 'State', 'Deadline']);

        foreach ($response as $task) {
            $deadline = $task->deadline['triggerDate'] ?? 'N/A';
            $currency = $task->currency['currency'] ?? 'N/A';

            $table->addRow([
                $task->id,
                substr($task->uuid, 0, 8) . '...',
                substr($task->title, 0, 40),
                $task->price,
                $currency,
                $task->state,
                $deadline,
            ]);
        }

        $table->render();

        $output->writeln("\n<info>Task Details:</info>");
        $output->writeln(str_repeat('-', 80));

        foreach ($response as $index => $task) {
            $output->writeln(sprintf(
                "\n<fg=cyan>Task #%d</> (ID: %d)",
                $index + 1,
                $task->id
            ));
            $output->writeln(sprintf('  <fg=yellow>UUID:</> %s', $task->uuid));
            $output->writeln(sprintf('  <fg=yellow>Title:</> %s', $task->title));
            $output->writeln(sprintf('  <fg=yellow>Description:</> %s', $task->description));
            $output->writeln(sprintf('  <fg=yellow>Price:</> $%.2f %s', $task->price, $task->currency['currency']));
            $output->writeln(sprintf('  <fg=yellow>State:</> %d', $task->state));
            $output->writeln(sprintf('  <fg=yellow>Deadline Type:</> %d', $task->deadline['type']));
            $output->writeln(sprintf('  <fg=yellow>Deadline Date:</> %s', $task->deadline['triggerDate']));
            $output->writeln(sprintf('  <fg=yellow>Coming Up:</> %s', $task->deadline['isComingUp'] ? 'Yes' : 'No'));

            $output->writeln('  <fg=green>Worker:</>');
            $output->writeln(sprintf('    Name: %s', $task->worker['name'] ?: 'Not provided'));
            $output->writeln(sprintf('    ID: %d', $task->worker['id']));
            $output->writeln(sprintf('    Email: %s', $task->worker['email']));
            $output->writeln(sprintf('    Verified: %s', $task->worker['isVerified'] ? 'Yes' : 'No'));

            if (!empty($task->worker['category']['title'])) {
                $output->writeln(sprintf('    Category: %s', $task->worker['category']['title']));
            }

            $output->writeln(str_repeat('-', 80));
        }

        // Option 3: Simple JSON output (useful for piping to other commands)
        // $output->writeln(json_encode($response, JSON_PRETTY_PRINT));

        // Option 4: Count summary
        $output->writeln(sprintf("\n<info>✓ Total tasks: %d</info>", count($response)));

        return Command::SUCCESS;
    }
}
