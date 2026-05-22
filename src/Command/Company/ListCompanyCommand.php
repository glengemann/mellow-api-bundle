<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Company;

use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
#[AsCommand(
    name: 'mellow:company:list',
    description: 'Retrieve the companies from Mellow.',
)]
class ListCompanyCommand extends Command
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
        $response = $client->company()->list();
        $companies = $response->items;

        if ([] === $companies) {
            $io->warning('No companies found.');

            return Command::SUCCESS;
        }

        $io->title('Mellow Companies');

        $table = new Table($output);
        $table->setHeaders(['ID', 'UUID', 'Company Name', 'Brand Name', 'Country', 'Currency', 'Status', 'Default', 'Active']);

        foreach ($companies as $company) {
            $table->addRow([
                $company->id,
                $company->uuid,
                $company->companyName,
                $company->brandName,
                $company->country,
                sprintf('%s (id: %d)', $company->currency['currency'], $company->currency['id']),
                $company->statusId,
                $company->isDefault ? '<info>Yes</info>' : 'No',
                $company->activated ? '<info>Yes</info>' : '<error>No</error>',
            ]);
        }

        $table->render();

        $io->info(sprintf(
            '%d company(ies) found (page %d of %d).',
            $response->pagination->total,
            $response->pagination->page,
            $response->pagination->pages,
        ));

        return Command::SUCCESS;
    }
}
