<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Lookup;

use Mellow\Api\Lookup\Response\ServiceAttributeResponse;
use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mellow:lookup:service-attributes',
    description: 'Retrieve task categories',
)]
class ListServiceAttributesCommand extends Command
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

        $response = $client->lookup()->serviceAttributes();

        $table = new Table($output);
        $table->setHeaders(['ID', 'Title (EN)', 'Description (EN)', 'Type', 'Attribute Type ID']);

        /** @var ServiceAttributeResponse $service */
        foreach ($response as $service) {
            $table->addRow([
                $service->id,
                $service->titleEn,
                $service->descriptionEn,
                $service->type,
                $service->attrTypeId,
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
