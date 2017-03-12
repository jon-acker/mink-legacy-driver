<?php

namespace carlosV2\LegacyDriver\LegacyApp;

use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

final class LegacyApp
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
     * @param string[]        $environmentVariables
     * @param string[]        $bootstrapScripts
     */
    public function __construct(
        $documentRoot,
        RouteCollection $controllers,
        array $environmentVariables,
        array $bootstrapScripts
    ) {
        $this->documentRoot = $documentRoot;
        $this->controllers = $controllers;
        $this->environmentVariables = $environmentVariables;
        $this->bootstrapScripts = $bootstrapScripts;
    }

    /**
     * @param Request $request
     */
    public function handle(Request $request)
    {
        $controller = $this->getFrontendControllerScript($request);

        chdir(dirname($controller));
        $this->setVariables($request);

        $this->bootstrapScripts[] = $controller;
        $this->bootstrapApp();
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getFrontendControllerScript(Request $request)
    {
        $parts = parse_url($request->getUri());
        $matcher = new UrlMatcher(
            $this->controllers,
            new RequestContext(
                '/',
                strtoupper($request->getMethod())
            )
        );
        $parameters = $matcher->match($parts['path']);
        return $parameters['file'];
    }

    /**
     * @param Request $request
     */
    private function setVariables(Request $request)
    {
        $variablesOrder = ini_get('variables_order');

        $this->setDefaultVariables();
        $length = strlen($variablesOrder);
        for ($i = 0; $i < $length; $i++) {
            switch ($variablesOrder[$i]) {
                case 'E':
                    $this->setEnvironmentVariables();
                    break;
                case 'G':
                    $this->setGetVariables($request);
                    break;
                case 'P':
                    $this->setPostVariables($request);
                    break;
                case 'C':
                    $this->setCookieVariables($request);
                    break;
                case 'S':
                    $this->setServerVariables($request);
                    break;
            };
        }

        foreach ($this->environmentVariables as $key => $value) {
            putenv(sprintf('%s=%s', $key, $value));
        }
    }

    private function setDefaultVariables()
    {
        $_REQUEST = array();
        $_ENV = array();
        $_GET = array();
        $_POST = array();
        $_COOKIE = array();
        $_SERVER = array();
    }

    private function setEnvironmentVariables()
    {
        $_ENV = $this->environmentVariables;
    }

    /**
     * @param Request $request
     */
    private function setGetVariables(Request $request)
    {
        if (strtoupper($request->getMethod()) === 'GET') {
            $_GET = $request->getParameters();
        }

        $_REQUEST = array_merge($_REQUEST, $_GET);
    }

    /**
     * @param Request $request
     */
    private function setPostVariables(Request $request)
    {
        if (strtoupper($request->getMethod()) === 'POST') {
            $_POST = $request->getParameters();
        }

        $_REQUEST = array_merge($_REQUEST, $_POST);
    }

    /**
     * @param Request $request
     */
    private function setCookieVariables(Request $request)
    {
        $_COOKIE = $request->getCookies();

        $_REQUEST = array_merge($_REQUEST, $_COOKIE);
    }

    /**
     * @param Request $request
     */
    private function setServerVariables(Request $request)
    {
        $_SERVER = $request->getServer();
        $_SERVER['DOCUMENT_ROOT'] = $this->documentRoot . '/';
        $_SERVER['SCRIPT_FILENAME'] = $this->getFrontendControllerScript($request);
        $_SERVER['SCRIPT_NAME'] = str_replace($this->documentRoot, '', $_SERVER['SCRIPT_FILENAME']);
        $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];

        $parts = parse_url($request->getUri());
        $_SERVER['REQUEST_SCHEME'] = strtolower($parts['scheme']);
        $_SERVER['HTTP_HOST'] = strtolower($parts['host']);
        $_SERVER['REQUEST_URI'] = $parts['path'];
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());

        if (isset($parts['query'])) {
            $_SERVER['QUERY_STRING'] = $parts['query'];
        }

        if ($_SERVER['HTTP_HOST'] === 'https') {
            $_SERVER['HTTPS'] = 'on';
        }
    }

    private function bootstrapApp()
    {
        foreach ($this->bootstrapScripts as $bootstrapScript) {
            require_once $bootstrapScript;
        }
    }
}
