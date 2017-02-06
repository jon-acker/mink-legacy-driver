<?php

namespace Jacker\LegacyDriver\Driver;

use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory;
use Jacker\LegacyDriver\ServiceContainer\LegacyDriverExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class LegacyFactory implements DriverFactory
{
    /**
     * {@inheritdoc}
     */
    public function getDriverName()
    {
        return 'legacy';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsJavascript()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->arrayNode('server_parameters')
                    ->useAttributeAsKey('key')
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDriver(array $config)
    {
        if (!class_exists('Behat\Mink\Driver\BrowserKitDriver')) {
            throw new \RuntimeException(
                'Install MinkBrowserKitDriver in order to use goutte driver.'
            );
        }
        
        return new Definition(LegacyAppDriver::class, array(
            new Reference(LegacyDriverExtension::KERNEL_ID),
            '%mink.base_url%',
        ));
    }
}
