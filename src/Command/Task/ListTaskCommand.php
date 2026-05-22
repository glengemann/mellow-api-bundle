<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Task;

use Mellow\Api\Task\Parameter\FilterParameters;
use Mellow\Api\TaskStatus;
use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
            ->addArgument('companyId', InputArgument::OPTIONAL, 'Company ID to filter tasks by')
            ->addOption('page', null, InputOption::VALUE_OPTIONAL, 'Page number', '1')
            ->addOption('size', null, InputOption::VALUE_OPTIONAL, 'Items per page', '20');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string|null $companyId */
        $companyId = $input->getArgument('companyId');
        if (null !== $companyId) {
            $companyId = (int) $companyId;
        }
        $client = $this->clientFactory->create($companyId);

        $page = max(1, (int) $input->getOption('page'));
        $size = max(1, (int) $input->getOption('size'));

        while (true) {
            $parameters = (new FilterParameters())
                ->page($page)
                ->size($size);
            $response = $client->task()->list($parameters);

            if ([] === $response->items) {
                $io->warning('No tasks found.');

                return Command::SUCCESS;
            }

            $table = new Table($output);
            $table->setHeaders(['ID', 'UUID', 'Title', 'Price', 'Currency', 'State', 'Deadline']);

            foreach ($response->items as $task) {
                $deadline = $task->deadline['triggerDate'] ?? 'N/A';
                $currency = $task->currency['currency'] ?? 'N/A';
                $state = TaskStatus::tryFrom($task->state);

                $table->addRow([
                    $task->id,
                    $task->uuid,
                    $task->title,
                    $task->price,
                    $currency,
                    sprintf('%s (%d)', $state?->name ?? 'UNKNOWN', $task->state),
                    $deadline,
                ]);
            }

            $table->render();
            $io->text(sprintf(
                'Page %d/%d | Per page: %d | Count: %d | Total: %d',
                $response->pagination->page,
                $response->pagination->pages,
                $response->pagination->perPage,
                $response->pagination->count,
                $response->pagination->total,
            ));

            if (!$input->isInteractive()) {
                return Command::SUCCESS;
            }

            $action = $io->choice(
                'Pagination',
                ['next', 'previous', 'goto', 'quit'],
                'next',
            );

            if ('quit' === $action) {
                return Command::SUCCESS;
            }

            if ('next' === $action) {
                $page = min($response->pagination->pages, $response->pagination->page + 1);
                continue;
            }

            if ('previous' === $action) {
                $page = max(1, $response->pagination->page - 1);
                continue;
            }

            $gotoPage = (int) $io->ask(
                sprintf('Go to page (1-%d)', $response->pagination->pages),
                (string) $response->pagination->page,
            );
            $page = max(1, min($response->pagination->pages, $gotoPage));
        }
    }
}
