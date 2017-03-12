<?php

namespace carlosV2\LegacyDriver;

use carlosV2\LegacyDriver\Exception\PhpCgiExecutableNotFoundException;
use carlosV2\LegacyDriver\LegacyApp\LegacyAppBuilder;
use carlosV2\LegacyDriver\Runner\RunCommand;
use Symfony\Component\BrowserKit\Client as BrowserKitClient;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
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

        return $this->parseResponse($process->getOutput());
    }

    /**
     * Compose the command to run with the same PHP binary that was used to run Behat
     *
     * @param object $request
     *
     * @return string
     */
    private function composeCommand($request)
    {
        return sprintf(
            '%s %s %s %s %s',
            $this->findPhpCgiBinary(),
            realpath(__DIR__ . self::RUNNER),
            RunCommand::NAME,
            $this->serializer->serialize($request),
            $this->serializer->serialize($this->legacyAppBuilder)
        );
    }

    /**
     * @return string
     */
    private function findPhpCgiBinary()
    {
        $finder = new ExecutableFinder();

        if ($binary = $finder->find('php-cgi')) {
            return $binary;
        }

        throw new PhpCgiExecutableNotFoundException();
    }

    /**
     * @param string $message
     *
     * @return Response
     */
    private function parseResponse($message)
    {
        $message = preg_replace('/^Status:/', 'HTTP/1.1', $message, 1);
        if (strpos($message, 'HTTP/1.1') !== 0) {
            $message = "HTTP/1.1 200 OK\r\n" . $message;
        }

        $response = \RingCentral\Psr7\parse_response($message);
        return new Response(
            $response->getBody()->getContents(),
            $response->getStatusCode(),
            $response->getHeaders()
        );
    }
}
