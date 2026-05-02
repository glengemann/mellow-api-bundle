<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Task;

use Mellow\Api\Task\Parameter\FilterParameters;
use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
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

    protected function configure(): void
    {
        $this
            ->addArgument('companyId', InputArgument::OPTIONAL, 'Company ID to filter tasks by');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $companyId */
        $companyId = $input->getArgument('companyId');
        if (null !== $companyId) {
            $companyId = (int) $companyId;
        }
        $client = $this->clientFactory->create($companyId);

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

        return Command::SUCCESS;
    }
}
