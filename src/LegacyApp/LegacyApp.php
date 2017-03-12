<?php

namespace carlosV2\LegacyDriver\LegacyApp;

use Symfony\Component\BrowserKit\Request;
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
     * @param string $documentRoot
     * @param Controllers $controllers
     * @param string[] $environmentVariables
     * @param string[] $bootstrapScripts
     */
    public function __construct(
        $documentRoot,
        Controllers $controllers,
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
        $this->setVariables($request);

        $this->bootstrapScripts[] = $this->controllers->getFront($request);

        $this->bootstrapApp();
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
            $parts = parse_url($request->getUri());
            if (isset($parts['query'])) {
                parse_str($parts['query'], $_GET);
            }
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
        $_SERVER['SCRIPT_FILENAME'] = $this->controllers->getFront($request);
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

        if ($_SERVER['REQUEST_SCHEME'] === 'https') {
            $_SERVER['HTTPS'] = 'on';
        }
    }

    private function bootstrapApp()
    {
        array_map(function(Script $script) { $script->load(); }, $this->bootstrapScripts);
    }
}
