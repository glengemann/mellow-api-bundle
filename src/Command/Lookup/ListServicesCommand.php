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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    protected function configure(): void
    {
        $this
            ->addOption('page', null, InputOption::VALUE_OPTIONAL, 'Page number', '1')
            ->addOption('size', null, InputOption::VALUE_OPTIONAL, 'Items per page', '20');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $client = $this->clientFactory->create();

        $page = max(1, (int) $input->getOption('page'));
        $size = max(1, (int) $input->getOption('size'));

        $parameters = (new ServiceAttributesParameters())
            ->page($page)
            ->size($size);
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
        $io->text(sprintf(
            'Page %d/%d | Per page: %d | Count: %d | Total: %d',
            $response->pagination->page,
            $response->pagination->pages,
            $response->pagination->perPage,
            $response->pagination->count,
            $response->pagination->total,
        ));

        return Command::SUCCESS;
    }
}
