<?php

namespace Jacker\LegacyDriver\LegacyApp;

final class LegacyAppBuilder
{
    /**
     * @var string
     */
    private $publicFolder;

    /**
     * @var string[]
     */
    private $environmentVariables;

    /**
     * @var string[]
     */
    private $bootstrapScripts;

    /**
     * @param string $publicFolder
     */
    public function __construct($publicFolder)
    {
        $this->publicFolder = $publicFolder;
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
            $this->publicFolder,
            $this->environmentVariables,
            $this->bootstrapScripts
        );
    }
}
