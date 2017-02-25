<?php

namespace carlosV2\LegacyDriver;

use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

abstract class ConfigurableDriverFactory implements DriverFactory
{
    const DOCUMENT_ROOT_KEY = 'document_root';
    const CONTROLLER_KEY = 'controller';
    const ENVIRONMENT_KEY = 'environment';
    const BOOTSTRAP_KEY = 'bootstrap';
    const MAPPING_KEY = 'mapping';

    /**
     * {@inheritdoc}
     */
    final public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->append($this->getDocumentRootConfiguration())
                ->append($this->getControllerConfiguration())
                ->append($this->getEnvironmentConfiguration())
                ->append($this->getBootstrapConfiguration())
            ->end()
        ;
    }

    /**
     * @return NodeDefinition
     */
    private function getDocumentRootConfiguration()
    {
        $node = new ScalarNodeDefinition(self::DOCUMENT_ROOT_KEY);

        return $node
            ->isRequired()
            ->beforeNormalization()
                ->always(function ($folder) {
                    return realpath($folder);
                })
            ->end()
            ->validate()
                ->ifTrue(function ($folder) {
                    return !is_dir($folder);
                })
                ->thenInvalid('Document root folder is not found. Please provide an existing folder.')
            ->end()
        ;
    }

    /**
     * @return NodeDefinition
     */
    private function getControllerConfiguration()
    {
        $node = new ArrayNodeDefinition(self::CONTROLLER_KEY);

        return $node
            ->isRequired()
            ->beforeNormalization()
                ->ifString()
                ->then(function ($controller) {
                    return array(
                        array(
                            'path' => '/{catchall}',
                            'file' => $controller,
                            'requirements' => array('catchall' => '.*')
                        )
                    );
                })
            ->end()
            ->requiresAtLeastOneElement()
            ->prototype('array')
                ->children()
                    ->scalarNode('path')->isRequired()->end()
                    ->scalarNode('file')
                        ->isRequired()
                        ->beforeNormalization()
                            ->always(function ($file) {
                                return realpath($file);
                            })
                        ->end()
                        ->validate()
                            ->ifTrue(function ($file) {
                                return !is_file($file);
                            })
                            ->thenInvalid('File controller is not found. Please provide an existing file.')
                        ->end()
                    ->end()
                    ->arrayNode('requirements')
                        ->useAttributeAsKey('parameter')
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('methods')
                        ->beforeNormalization()
                            ->always(function ($methods) {
                                return array_unique($methods);
                            })
                        ->end()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @return NodeDefinition
     */
    private function getEnvironmentConfiguration()
    {
        $node = new ArrayNodeDefinition(self::ENVIRONMENT_KEY);

        return $node
            ->useAttributeAsKey('parameter')
            ->prototype('scalar')->end()
        ;
    }

    /**
     * @return NodeDefinition
     */
    private function getBootstrapConfiguration()
    {
        $node = new ArrayNodeDefinition(self::BOOTSTRAP_KEY);

        return $node
            ->beforeNormalization()
                ->ifString()
                ->then(function ($bootstrap) {
                    return array($bootstrap);
                })
            ->end()
            ->beforeNormalization()
                ->always(function ($boostrap) {
                    return array_unique(array_map(function ($file) {
                        return realpath($file);
                    }, $boostrap));
                })
            ->end()
            ->prototype('scalar')
                ->validate()
                    ->ifTrue(function ($file) {
                        return !is_file($file);
                    })
                    ->thenInvalid('Bootstrap file is not found. Please provide an existing file.')
                ->end()
            ->end()
        ;
    }
}
