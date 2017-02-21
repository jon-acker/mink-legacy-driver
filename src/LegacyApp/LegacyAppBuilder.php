<?php

namespace Jacker\LegacyDriver\LegacyApp;

final class LegacyAppBuilder
{
    /**
     * @var string
     */
    private $documentRoot;

    /**
     * @var string[]
     */
    private $environmentVariables;

    /**
     * @var string[]
     */
    private $bootstrapScripts;

    /**
     * @var string[]
     */
    private $mappingClasses;

    /**
     * @param string $documentRoot
     */
    public function __construct($documentRoot)
    {
        $this->documentRoot = $documentRoot;
        $this->environmentVariables = array();
        $this->bootstrapScripts = array();
        $this->mappingClasses = array();
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
     * @param string[] $mappingClasses
     */
    public function addMappingClasses(array $mappingClasses)
    {
        $this->mappingClasses = $mappingClasses;
    }

    /**
     * @return LegacyApp
     */
    public function build()
    {
        return new LegacyApp(
            $this->documentRoot,
            $this->environmentVariables,
            $this->bootstrapScripts,
            $this->mappingClasses
        );
    }
}
