<?php

namespace carlosV2\LegacyDriver;

use carlosV2\LegacyDriver\LegacyApp\LegacyAppBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class DriverFactory extends ConfigurableDriverFactory
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
    public function buildDriver(array $config)
    {
        $legacyAppBuilder = new LegacyAppBuilder($config[self::DOCUMENT_ROOT_KEY]);
        $legacyAppBuilder->addEnvironmentVariables($config[self::ENVIRONMENT_KEY]);
        $legacyAppBuilder->addBootstrapScripts($config[self::BOOTSTRAP_KEY]);
        $legacyAppBuilder->addMappingClasses($config[self::MAPPING_KEY]);

        return new Definition('Behat\Mink\Driver\BrowserKitDriver', array(
            $this->buildClient($this->composerRouteCollection($config[self::CONTROLLER_KEY]), $legacyAppBuilder),
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
            $collection->add(
                md5(serialize($controller)),
                new Route(
                    $controller['path'],
                    array('file' => $controller['file']),
                    $controller['requirements'],
                    array(),
                    '',
                    array(),
                    $controller['methods']
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
        return new Definition('carlosV2\LegacyDriver\Client', array(
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
        return new Definition('carlosV2\LegacyDriver\Serializer');
    }
}
