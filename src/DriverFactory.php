<?php

namespace Jacker\LegacyDriver;

use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory as MinkDriverFactory;
use Jacker\LegacyDriver\LegacyApp\LegacyAppBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class DriverFactory implements MinkDriverFactory
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
        $configuration = new Configuration();
        $configuration->configure($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function buildDriver(array $config)
    {
        $legacyAppBuilder = new LegacyAppBuilder($config[Configuration::PUBLIC_FOLDER_KEY]);
        $legacyAppBuilder->addEnvironmentVariables($config[Configuration::ENVIRONMENT_KEY]);
        $legacyAppBuilder->addBootstrapScripts($config[Configuration::BOOTSTRAP_KEY]);

        return new Definition('Behat\Mink\Driver\BrowserKitDriver', array(
            $this->buildClient($this->composerRouteCollection($config['controller']), $legacyAppBuilder),
            '%mink.base_url%',
        ));
    }

    /**
     * @param array $controllers
     *
     * @return RouteCollection
     */
    private function composerRouteCollection(array $controllers)
    {
        $collection = new RouteCollection();

        foreach ($controllers as $controller) {
            $requirements = array();
            if (isset($controller['requirements'])) {
                $requirements = $controller['requirements'];
            }

            $methods = array();
            if (isset($controller['methods'])) {
                $methods = $controller['methods'];
            }

            $collection->add(
                md5(serialize($controller)),
                new Route(
                    $controller['path'],
                    array('file' => $controller['file']),
                    $requirements,
                    array(),
                    '',
                    array(),
                    $methods
                )
            );
        }

        return $collection;
    }

    /**
     * @param RouteCollection  $controllers
     * @param LegacyAppBuilder $legacyAppBuilder
     *
     * @return Definition
     */
    private function buildClient(RouteCollection $controllers, LegacyAppBuilder $legacyAppBuilder)
    {
        return new Definition('Jacker\LegacyDriver\Client', array(
            $this->buildSerializer(),
            $controllers,
            $legacyAppBuilder
        ));
    }

    /**
     * @return Definition
     */
    private function buildSerializer()
    {
        return new Definition('Jacker\LegacyDriver\Serializer');
    }
}
