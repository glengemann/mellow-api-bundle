<?php

declare(strict_types=1);

namespace MellowApiBundle\Command\Lookup;

use Mellow\Api\Lookup\Parameter\ServiceAttributesParameters;
use Mellow\Api\Lookup\Response\ServiceCollectionResponse;
use Mellow\Api\Lookup\Response\ServiceResponse;
use MellowApiBundle\ClientFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mellow:lookup:services',
    description: 'Retrieve the names of the services',
)]
class ListServicesCommand extends Command
{
    public function __construct(
        private readonly ClientFactory $clientFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->clientFactory->create();

        $parameters = (new ServiceAttributesParameters())
            ->page(1)
            ->size(20);
        /** @var ServiceCollectionResponse $response */
        $response = $client->lookup()->services($parameters);

        $table = new Table($output);
        $table->setHeaders(['ID', 'Title (EN)', 'Title Doc (EN)']);

        /** @var ServiceResponse $service */
        foreach ($response->items as $service) {
            $table->addRow([
                $service->id,
                $service->titleEn,
                $service->titleDocEn,
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
