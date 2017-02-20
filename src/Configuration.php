<?php

namespace Jacker\LegacyDriver;

final class Configuration
{
    /**
     * @var string
     */
    private $publicFolder;

    /**
     * @var array
     */
    private $environment;

    /**
     * @param string $publicFolder
     */
    public function setPublicFolder($publicFolder)
    {
        $this->publicFolder = $publicFolder;
    }

    /**
     * @return string
     */
    public function getPublicFolder()
    {
        return $this->publicFolder;
    }

    /**
     * @return array
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param array $environment
     */
    public function setEnvironment(array $environment)
    {
        $this->environment = $environment;
    }
}
