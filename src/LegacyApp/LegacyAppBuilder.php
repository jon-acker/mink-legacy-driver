<?php

namespace carlosV2\LegacyDriver\LegacyApp;

use Symfony\Component\Routing\RouteCollection;

final class LegacyAppBuilder
{
    /**
     * @var string
     */
    private $documentRoot;

    /**
     * @var RouteCollection
     */
    private $controllers;

    /**
     * @var string[]
     */
    private $environmentVariables;

    /**
     * @var string[]
     */
    private $bootstrapScripts;

    /**
     * @param string          $documentRoot
     * @param RouteCollection $controllers
     */
    public function __construct($documentRoot, RouteCollection $controllers)
    {
        $this->documentRoot = $documentRoot;
        $this->controllers = $controllers;
        $this->environmentVariables = array();
        $this->bootstrapScripts = array();
    }

    /**
     * @param string[] $environmentVariables
     */
    public function addEnvironmentVariables(array $environmentVariables)
    {
        $this->environmentVariables = $environmentVariables;
    }

    /**
     * @param string[] $bootstrapScripts
     */
    public function addBootstrapScripts(array $bootstrapScripts)
    {
        $this->bootstrapScripts = $bootstrapScripts;
    }

    /**
     * @return LegacyApp
     */
    public function build()
    {
        return new LegacyApp(
            $this->documentRoot,
            $this->controllers,
            $this->environmentVariables,
            $this->bootstrapScripts
        );
    }
}
