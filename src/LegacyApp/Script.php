<?php

namespace carlosV2\LegacyDriver\LegacyApp;

class Script
{
    /**
     * @var array
     */
    private $script;

    /**
     * @param string $script
     */
    public function __construct($script)
    {
        $this->script = $script;
    }

    /**
     */
    public function load()
    {
        chdir(dirname($this->script));
        require_once $this->script;
    }

    public function __toString()
    {
         return $this->script;
    }
}
