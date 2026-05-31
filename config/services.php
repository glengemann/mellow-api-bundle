<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Mellow\LoginInterface;
use Mellow\Store\FileTokenStorage;
use Mellow\Store\Psr6TokenStore;
use Mellow\Store\TokenStoreInterface;
use MellowApiBundle\ClientFactory;
use MellowApiBundle\Command\Company\GetCompanyBalanceCommand;
use MellowApiBundle\Command\Company\ListCompanyCommand;
use MellowApiBundle\Command\CreateWebhookCommand;
use MellowApiBundle\Command\Freelancer\ListFreelancerCommand;
use MellowApiBundle\Command\Login\LoginCommand;
use MellowApiBundle\Command\Lookup\ListServiceAttributesCommand;
use MellowApiBundle\Command\Lookup\ListServicesCommand;
use MellowApiBundle\Command\Profile\RetrievingProfileCommand;
use MellowApiBundle\Command\RemoveWebhookCommand;
use MellowApiBundle\Command\RetrieveWebhookCommand;
use MellowApiBundle\Command\Task\ListTaskCommand;
use MellowApiBundle\Command\Task\RetrieveTaskCommand;
use MellowApiBundle\Login\LoginService;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('mellow.token_storage.psr6', Psr6TokenStore::class)
        ->args([
            service('cache.app'),
        ])
        ->alias(TokenStoreInterface::class, 'mellow.token_storage.psr6')

        ->set('mellow_api.client', ClientFactory::class)
        ->args([
            param('mellow.url'),
            param('mellow.username'),
            param('mellow.password'),
            service('mellow.token_storage.psr6'),
        ])

        ->set('mellow_api.login_service', LoginService::class)
        ->args([
            service('mellow_api.client'),
            service('mellow.token_storage.psr6'),
        ])
        ->alias(LoginInterface::class, 'mellow_api.login_service')

        ->alias(ClientFactory::class, 'mellow_api.client')
        ->alias(FileTokenStorage::class, 'mellow.token_storage.file')

        ->set('mellow_api.login_command', LoginCommand::class)
        ->args([
            service('mellow_api.login_service'),
        ])
        ->tag('console.command')

        ->set('mellow_api.webhook_retrieve_command', RetrieveWebhookCommand::class)
            ->args([
                service('mellow_api.client'),
            ])
            ->tag('console.command')
        ->set('mellow_api.webhook_create_command', CreateWebhookCommand::class)
            ->args([
                service('mellow_api.client'),
                param('mellow.webhook_url'),
            ])
            ->tag('console.command')
        ->set('mellow_api.webhook_remove_command', RemoveWebhookCommand::class)
            ->args([
                service('mellow_api.client'),
            ])
            ->tag('console.command')

        ->set('mellow_api.task_retrieve_command', RetrieveTaskCommand::class)
            ->args([
                service('mellow_api.client'),
            ])
            ->tag('console.command')
        ->set('mellow_api.list_task_command', ListTaskCommand::class)
            ->args([
                service('mellow_api.client'),
            ])
            ->tag('console.command')

        ->set('mellow_api.lookup_list_service_command', ListServicesCommand::class)
            ->args([
                service('mellow_api.client'),
            ])
            ->tag('console.command')
        ->set('mellow_api.lookup_list_service_attributes_command', ListServiceAttributesCommand::class)
            ->args([
                service('mellow_api.client'),
            ])
            ->tag('console.command')

        ->set('mellow_api.company_list_command', ListCompanyCommand::class)
        ->args([
            service('mellow_api.client'),
        ])
        ->tag('console.command')

        ->set('mellow_api.company_balance_command', GetCompanyBalanceCommand::class)
        ->args([
            service('mellow_api.client'),
        ])
        ->tag('console.command')

        ->set('mellow_api.profile_command', RetrievingProfileCommand::class)
        ->args([
            service('mellow_api.client'),
        ])
        ->tag('console.command')

        ->set('mellow_api.freelancer_list_command', ListFreelancerCommand::class)
        ->args([
            service('mellow_api.client'),
        ])
        ->tag('console.command')
    ;
};
