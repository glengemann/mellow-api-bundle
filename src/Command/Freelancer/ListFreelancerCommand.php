<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Freelancer;

use Mellow\Api\Freelancer\Parameter\FreelancerFilter;
use Mellow\Api\Freelancer\Parameter\FreelancerListParameters;
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
    name: 'mellow:freelancer:list',
    description: 'List freelancers',
)]
class ListFreelancerCommand extends Command
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'companyId',
                InputArgument::OPTIONAL,
                'Company ID to filter candidates by',
            )
            ->addOption(
                'page',
                null,
                InputArgument::OPTIONAL,
                'Page number',
                '1',
            )
            ->addOption(
                'size',
                null,
                InputArgument::OPTIONAL,
                'Items per page',
                '20',
            )
            ->addOption(
                'is-verified',
                null,
                InputOption::VALUE_OPTIONAL,
                'Filter by verified status (true or false)',
                null,
            )
            ->addOption(
                'is-invite-sent',
                null,
                InputOption::VALUE_OPTIONAL,
                'Filter by invite sent status (true or false)'
            )
            ->addOption(
                'date-invited-from',
                null,
                InputOption::VALUE_OPTIONAL,
                'Filter from date (Y-m-d)'
            )
            ->addOption(
                'date-invited-to',
                null,
                InputOption::VALUE_OPTIONAL,
                'Filter to date (Y-m-d)'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('List Freelancers');

        $companyId = $input->getArgument('companyId');
        if (null !== $companyId) {
            $companyId = (int) $companyId;
        }
        $api = $this->clientFactory->create($companyId);

        $page = (int) $input->getOption('page');
        $size = (int) $input->getOption('size');

        while (true) {
            $parameters = (new FreelancerListParameters())
                ->page($page)
                ->size($size);

            $filter = new FreelancerFilter();
            $hasFilter = false;

            if (null !== $value = $input->getOption('is-verified')) {
                $filter->isVerified((bool) $value);
                $hasFilter = true;
            }

            if (null !== $value = $input->getOption('is-invite-sent')) {
                $filter->isInviteEmailSent((bool) $value);
                $hasFilter = true;
            }

            if (null !== $value = $input->getOption('date-invited-from')) {
                $filter->dateInvitedFrom(new \DateTimeImmutable($value));
                $hasFilter = true;
            }

            if (null !== $value = $input->getOption('date-invited-to')) {
                $filter->dateInvitedTo(new \DateTimeImmutable($value));
                $hasFilter = true;
            }

            if (true === $hasFilter) {
                $parameters->filter($filter);
            }

            $freelancers = $api->freelancer()->list($parameters);

            if (0 === count($freelancers->items)) {
                $io->warning('No freelancers found.');

                return Command::SUCCESS;
            }

            $table = new Table($output);
            $table->setHeaders(['ID']);

            foreach ($freelancers->items as $freelancer) {
                $table->addRow([
                    $freelancer->id,
                ]);
            }

            $table->render();

            $io->text(sprintf(
                'Page %d/%d | Per page: %d | Count: %d | Total: %d',
                $freelancers->pagination->page,
                $freelancers->pagination->pages,
                $freelancers->pagination->perPage,
                $freelancers->pagination->count,
                $freelancers->pagination->total,
            ));

            if (false === $input->isInteractive()) {
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
                $page = min($freelancers->pagination->pages, $freelancers->pagination->page + 1);

                continue;
            }

            if ('previous' === $action) {
                $page = max(1, $freelancers->pagination->page - 1);

                continue;
            }

            $gotoPage = (int) $io->ask(
                sprintf('Go to page (1-%d)', $freelancers->pagination->pages),
                (string) $freelancers->pagination->page,
            );
            $page = max(1, min($freelancers->pagination->pages, $gotoPage));
        }
    }
}
