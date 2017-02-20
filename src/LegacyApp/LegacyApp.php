<?php

namespace Jacker\LegacyDriver\LegacyApp;

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
     * @param string   $publicFolder
     * @param string[] $environmentVariables
     * @param string[] $bootstrapScripts
     */
    public function __construct($publicFolder, array $environmentVariables, array $bootstrapScripts)
    {
        $this->publicFolder = $publicFolder;
        $this->environmentVariables = $environmentVariables;
        $this->bootstrapScripts = $bootstrapScripts;
    }

    /**
     * @param Request $request
     */
    public function handle(Request $request)
    {
        $this->setVariables($request);

        $serverVariables = $request->getServer();

        chdir($this->publicFolder);
        require_once $serverVariables['SCRIPT_FILENAME'];
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
            call_user_func(array($this, $this->variablesCallablesMap[$variablesOrder[$i]]), $request);
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

        foreach ($this->environmentVariables as $key => $value) {
            putenv(sprintf('%s=%s', $key, $value));
        }
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
        $_SERVER['DOCUMENT_ROOT'] = $this->publicFolder . '/';
        $_SERVER['SCRIPT_NAME'] = str_replace($this->publicFolder, '', $_SERVER['SCRIPT_FILENAME']);

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
