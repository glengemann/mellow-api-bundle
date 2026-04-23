<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use MellowApiBundle\ClientFactory;
use MellowApiBundle\Command\CreateWebhookCommand;
use MellowApiBundle\Command\RemoveWebhookCommand;
use MellowApiBundle\Command\RetrieveWebhookCommand;
use MellowApiBundle\Command\Task\RetrieveTaskCommand;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('mellow_api.client', ClientFactory::class)
        ->arg('$url', param('mellow.url'))

        ->alias(ClientFactory::class, 'mellow_api.client')

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
    ;
};
