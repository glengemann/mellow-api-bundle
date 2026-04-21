<?php

declare(strict_types=1);

namespace MellowApiBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class MellowApiBundle extends AbstractBundle
{
    protected string $extensionAlias = 'mellow';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('api')
                    ->children()
                        ->scalarNode('username')
                            ->info('The username for the Mellow API')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('password')
                            ->info('The password for the Mellow API')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('url')
                            ->info('The base URL of the Mellow API')
                            ->defaultValue('https://my.mellow.io/api')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->children()
                ->arrayNode('webhook')
                    ->children()
                        ->scalarNode('secret')
                            ->info('The secret for the Mellow webhook')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->end()
                        ->scalarNode('url')
                            ->info('The URL for the Mellow webhook')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function build(ContainerBuilder $container): void
    {
        /*
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createXmlMappingDriver(
                [__DIR__ . '/../config/doctrine/mapping' => 'MellowApiBundle\Model'],
            )
        );
        */
    }

    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder,
    ): void {
        $container->import('../config/services.php');

        $builder->setParameter('mellow.url', $config['api']['url']);
        $builder->setParameter('mellow.username', $config['api']['username']);
        $builder->setParameter('mellow.password', $config['api']['password']);
        $builder->setParameter('mellow.webhook_url', $config['webhook']['url']);
        $builder->setParameter('mellow.webhook_secret', $config['webhook']['secret']);
    }
}
