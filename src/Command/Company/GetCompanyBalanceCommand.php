<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Company;

use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mellow:company:balance',
    description: 'Retrieve the company balance from Mellow.',
)]
class GetCompanyBalanceCommand extends Command
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
                'The ID of the company to retrieve the balance for.',
            )
            ->setName('mellow:company:balance')
            ->setDescription('Retrieve the company balance from Mellow.');
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
        $balance = $client->company()->balance();

        $io->title('Company Balance');
        $io->definitionList(
            ['Balance ID' => (string) $balance->id],
            ['Currency' => sprintf('%s (id: %d)', $balance->currency['currency'], $balance->currency['id'])],
            ['Show VAT' => $balance->showVat ? 'Yes' : 'No'],
            ['Balance Amount' => (string) $balance->balanceAmount],
            ['Balance Amount VAT' => (string) $balance->balanceAmountVat],
            ['Hold Amount' => (string) $balance->holdAmount],
            ['Hold Amount VAT' => (string) $balance->holdAmountVat],
            ['To Pay Amount' => (string) $balance->toPayAmount],
            ['To Pay Amount VAT' => (string) $balance->toPayAmountVat],
        );

        return Command::SUCCESS;
    }
}
