<?php

namespace Jacker\LegacyDriver;

final class Configuration
{
    /**
     * @var string
     */
    private $publicFolder;

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
}
