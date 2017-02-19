<?php

namespace Jacker\LegacyDriver;

use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory as MinkDriverFactory;
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
        $builder
            ->children()
                ->variableNode('bootstrap')
                    ->beforeNormalization()
                        ->always(function ($bootstrap) {
                            if (is_string($bootstrap)) {
                                $bootstrap = array($bootstrap);
                            }

                            return $bootstrap;
                        })
                    ->end()
                    ->validate()
                        ->ifTrue(function ($scripts) {
                            if (!is_array($scripts)) {
                                return true;
                            }

                            foreach ($scripts as $script) {
                                if (!is_string($script)) {
                                    return true;
                                }
                            }

                            return false;
                        })
                        ->thenInvalid('Bootstrap key must be an string or an array of strings.')
                    ->end()
                ->end()
                ->variableNode('controller')
                    ->beforeNormalization()
                        ->always(function ($controller) {
                            if (is_string($controller)) {
                                $controller = array(array(
                                    'path' => '/{catchall}',
                                    'file' => $controller,
                                    'requirements' => array('catchall' => '.*')
                                ));
                            }

                            return $controller;
                        })
                    ->end()
                    ->validate()
                        ->ifTrue(function ($controllers) {
                            return !is_array($controllers);
                        })
                        ->thenInvalid('Controller configuration must be an string or an array.')
                        ->ifTrue(function ($controllers) {
                            foreach ($controllers as $controller) {
                                if (!array_key_exists('path', $controller) || !array_key_exists('file', $controller)) {
                                    return true;
                                }

                                if (!is_string($controller['path']) || !is_string($controller['file'])) {
                                    return true;
                                }
                            }

                            return false;
                        })
                        ->thenInvalid('Controllers must have the `path` and `file` keys as string.')
                        ->ifTrue(function ($controllers) {
                            foreach ($controllers as $controller) {
                                if (array_key_exists('requirements', $controller) && !is_array($controller['requirements'])) {
                                    return true;
                                }

                                if (array_key_exists('methods', $controller) && !is_array($controller['methods'])) {
                                    return true;
                                }
                            }

                            return false;
                        })
                        ->thenInvalid('Controllers keys `requirements` and `methods`, if defined,  must be arrays.')
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDriver(array $config)
    {
        return new Definition('Behat\Mink\Driver\BrowserKitDriver', array(
            $this->buildClient($this->composerRouteCollection($config['controller'])),
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
     * @param RouteCollection $controllers
     *
     * @return Definition
     */
    private function buildClient(RouteCollection $controllers)
    {
        return new Definition('Jacker\LegacyDriver\Client', array(
            $this->buildSerializer(),
            $controllers
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
