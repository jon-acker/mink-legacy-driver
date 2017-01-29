<?php

namespace MageTest\LegacyDriver\ServiceContainer;

use Behat\MinkExtension\ServiceContainer\MinkExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use MageTest\LegacyDriver\Driver\MageApp;
use MageTest\LegacyDriver\Driver\Magento2Factory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class LegacyDriverExtension implements Extension
{
    
    const CONFIG_KEY = 'magento2driver';

    const KERNEL_ID = 'behat.magento2.driver.app';

    private $magentoDriverFactory;

    /**
     * LegacyDriverExtension constructor.
     */
    public function __construct()
    {

        $this->magentoDriverFactory = new Magento2Factory();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return self::CONFIG_KEY;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // nothing to do here
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        /** @var MinkExtension $mink */
        $mink = $extensionManager->getExtension(MinkExtension::MINK_ID);
        $mink->registerDriverFactory(
            $this->magentoDriverFactory
        );

    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
//        $builder
//            ->children()
//                ->scalarNode(Config::CONFIG_KEY_MAGENTO_BOOTSTRAP_PATH)
//                    ->defaultValue(getcwd() . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php')
//                ->end()
//                ->arrayNode(Config::CONFIG_KEY_MAGENTO_CONFIGS)
//                    ->prototype('array')
//                        ->children()
//                            ->scalarNode('path')
//                                ->isRequired()
//                                ->cannotBeEmpty()
//                            ->end()
//                            ->scalarNode('value')
//                                ->isRequired()
//                            ->end()
//                            ->enumNode('scope_type')
//                                ->values(array('default', 'stores', 'websites'))
//                                ->defaultValue('default')
//                            ->end()
//                            ->scalarNode('scope_code')
//                                ->defaultValue(null)
//                            ->end()
//                        ->end()
//                    ->end()
//                ->end()
//            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('services.xml');

        $container->setDefinition(
            self::KERNEL_ID,
            new Definition(MageApp::class, array($container))
        );
    }
}
