<?php

namespace Jacker\LegacyDriver\Runner;

use Jacker\LegacyDriver\Configuration;
use Symfony\Component\BrowserKit\Request;

final class LegacyApp
{
    /**
     * @var array
     */
    private $variablesCallablesMap = array(
        'E' => 'setEnvironmentVariables',
        'G' => 'setGetVariables',
        'P' => 'setPostVariables',
        'C' => 'setCookieVariables',
        'S' => 'setServerVariables'
    );

    /**
     * @param Request       $request
     * @param Configuration $configuration
     */
    public function handle(Request $request, Configuration $configuration)
    {
        $this->setVariables($request, $configuration);

        $serverVariables = $request->getServer();

        chdir($configuration->getPublicFolder());
        require_once $serverVariables['SCRIPT_FILENAME'];
    }

    /**
     * @param Request       $request
     * @param Configuration $configuration
     */
    private function setVariables(Request $request, Configuration $configuration)
    {
        $variablesOrder = ini_get('variables_order');

        $this->setDefaultVariables();
        $length = strlen($variablesOrder);
        for ($i = 0; $i < $length; $i++) {
            call_user_func(array($this, $this->variablesCallablesMap[$variablesOrder[$i]]), $request, $configuration);
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

    /**
     * @param Request $request
     */
    private function setEnvironmentVariables(Request $request)
    {
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
     * @param Request       $request
     * @param Configuration $configuration
     */
    private function setServerVariables(Request $request, Configuration $configuration)
    {
        $_SERVER = $request->getServer();
        $_SERVER['DOCUMENT_ROOT'] = $configuration->getPublicFolder() . '/';
        $_SERVER['SCRIPT_NAME'] = str_replace($configuration->getPublicFolder(), '', $_SERVER['SCRIPT_FILENAME']);

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
}
