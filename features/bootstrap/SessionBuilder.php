<?php

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Session;
use carlosV2\LegacyDriver\Client;
use carlosV2\LegacyDriver\LegacyApp\Controllers;
use carlosV2\LegacyDriver\LegacyApp\LegacyAppBuilder;
use carlosV2\LegacyDriver\Serializer;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class SessionBuilder
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
    private $environment;

    /**
     * @var string[]
     */
    private $bootstrapScripts;

    /**
     * @param string $documentRoot
     */
    public function __construct($documentRoot)
    {
        $this->documentRoot = $documentRoot;
        $this->controllers = new Controllers(new RouteCollection());
        $this->environment = array();
        $this->bootstrapScripts = array();
    }

    /**
     * @param Route $route
     */
    public function addController(Route $route)
    {
        $this->controllers->add($route);
    }

    /**
     * @param string $variable
     * @param string $value
     */
    public function addEnvironmentVariable($variable, $value)
    {
        $this->environment[$variable] = $value;
    }

    /**
     * @param string $bootstrapScript
     */
    public function addBootstrapScript($bootstrapScript)
    {
        $this->bootstrapScripts[] = $bootstrapScript;
    }

    /**
     * @return Session
     */
     public function build()
     {
         $client = new Client(
             $this->buildLegacyAppBuilder(),
             new Serializer()
         );

         return new Session(new BrowserKitDriver($client));
     }

    /**
     * @return LegacyAppBuilder
     */
     private function buildLegacyAppBuilder()
     {
         $legacyAppBuilder = new LegacyAppBuilder(
             $this->documentRoot,
             $this->controllers
         );

         $legacyAppBuilder->addEnvironmentVariables($this->environment);
         $legacyAppBuilder->addBootstrapScripts($this->bootstrapScripts);

         return $legacyAppBuilder;
     }
}
