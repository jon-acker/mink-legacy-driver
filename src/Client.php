<?php

namespace carlosV2\LegacyDriver;

use carlosV2\LegacyDriver\LegacyApp\LegacyAppBuilder;
use carlosV2\LegacyDriver\Runner\RunCommand;
use Symfony\Component\BrowserKit\Client as BrowserKitClient;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class Client extends BrowserKitClient
{
    const RUNNER = '/Runner/run.php';

    /**
     * @var LegacyAppBuilder
     */
    private $legacyAppBuilder;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param LegacyAppBuilder $legacyAppBuilder
     * @param Serializer       $serializer
     */
    public function __construct(LegacyAppBuilder $legacyAppBuilder, Serializer $serializer)
    {
        parent::__construct();

        $this->legacyAppBuilder = $legacyAppBuilder;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRequest($request)
    {
        $process = new Process($this->composeCommand($request));
        $process->setInput($request->getContent());
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return HttpParser::createResponseFrom($process->getOutput());
    }

    /**
     * Compose the command to run with the same PHP binary that was used to run Behat
     *
     * @param Request $request
     *
     * @return string
     */
    private function composeCommand(Request $request)
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
