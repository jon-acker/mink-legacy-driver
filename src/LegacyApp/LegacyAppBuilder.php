<?php

namespace carlosV2\LegacyDriver\LegacyApp;

final class LegacyAppBuilder
{
    /**
     * @var string
     */
    private $documentRoot;

    /**
     * @var Controllers
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
     * @param string $documentRoot
     * @param Controllers $controllers
     */
    public function __construct($documentRoot, Controllers $controllers)
    {
        $this->documentRoot = $documentRoot;
        $this->controllers = $controllers;
        $this->environmentVariables = array();
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
        $this->bootstrapScripts = array_map(function($script) {
            return new Script($script);
        }, $bootstrapScripts);
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
