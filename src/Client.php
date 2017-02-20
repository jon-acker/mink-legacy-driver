<?php

namespace Jacker\LegacyDriver;

use Jacker\LegacyDriver\LegacyApp\LegacyAppBuilder;
use Jacker\LegacyDriver\Runner\RunCommand;
use Symfony\Component\BrowserKit\Client as BrowserKitClient;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

final class Client extends BrowserKitClient
{
    const RUNNER = '/Runner/run.php';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var RouteCollection
     */
    private $controllers;

    /**
     * @var LegacyAppBuilder
     */
    private $legacyAppBuilder;

    /**
     * @param Serializer       $serializer
     * @param RouteCollection  $controllers
     * @param LegacyAppBuilder $legacyAppBuilder
     */
    public function __construct(
        Serializer $serializer,
        RouteCollection $controllers,
        LegacyAppBuilder $legacyAppBuilder
    ) {
        parent::__construct();

        $this->serializer = $serializer;
        $this->controllers = $controllers;
        $this->legacyAppBuilder = $legacyAppBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRequest($request)
    {
        $command = $this->composeCommand($this->includeScriptFileInto($request));

        $process = new Process($command);
        $process->setInput($request->getContent());
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return HttpParser::createResponseFrom($process->getOutput());
    }

    /**
     * @param Request $request
     *
     * @return Request
     */
    private function includeScriptFileInto(Request $request)
    {
        return new Request(
            $request->getUri(),
            $request->getMethod(),
            $request->getParameters(),
            $request->getFiles(),
            $request->getCookies(),
            array_merge(array(
                'SCRIPT_FILENAME' => $this->getScriptFilename($request)
            ), $request->getServer()),
            $request->getContent()
        );
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getScriptFilename(Request $request)
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

        return realpath($parameters['file']);
    }

    /**
     * Compose the command to run with the same PHP binary that was used to run Behat
     *
     * @param Request $request
     *
     * @return string
     */
    private function composeCommand($request)
    {
        $binary = $_SERVER['_'];
        if (php_sapi_name() !== 'cgi-fcgi') {
            $binary .= '-cgi';
        }

        return sprintf(
            '%s %s %s %s %s',
            $binary,
            realpath(__DIR__ . self::RUNNER),
            RunCommand::NAME,
            $this->serializer->serialize($request),
            $this->serializer->serialize($this->legacyAppBuilder)
        );
    }
}
