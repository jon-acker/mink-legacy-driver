<?php

namespace Jacker\LegacyDriver;

use Jacker\LegacyDriver\Runner\RunCommand;
use Symfony\Component\BrowserKit\Client as BrowserKitClient;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

final class Client extends BrowserKitClient
{
    const RUNNER = '/Runner/run.php';
    const HTTP_VERSION = 'HTTP/1.1';
    const DEFAULT_STATUS_CODE = '200 OK';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var RouteCollection
     */
    private $controllers;

    /**
     * @param Serializer      $serializer
     * @param RouteCollection $controllers
     */
    public function __construct(Serializer $serializer, RouteCollection $controllers)
    {
        parent::__construct();

        $this->serializer = $serializer;
        $this->controllers = $controllers;
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

        return $this->parseResponse($process->getOutput());
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
            '%s %s %s %s',
            $binary,
            realpath(__DIR__ . self::RUNNER),
            RunCommand::NAME,
            $this->serializer->serialize($request)
        );
    }

    /**
     * @param string $message
     *
     * @return Response
     */
    private function parseResponse($message)
    {
        $data = $this->_parse_message($message);
        if (strpos($data['start-line'], 'Status:') === 0) {
            $data['start-line'] = str_replace('Status:', self::HTTP_VERSION, $data['start-line']);
        } else {
            $data['start-line'] = self::HTTP_VERSION . ' ' . self::DEFAULT_STATUS_CODE;
        }

        $parts = explode(' ', $data['start-line'], 3);

        return new Response(
            $data['body'],
            $parts[1],
            $data['headers']
        );
    }

    private function _parse_message($message)
    {
        if (!$message) {
            throw new \InvalidArgumentException('Invalid message');
        }

        // Iterate over each line in the message, accounting for line endings
        $lines = preg_split('/(\\r?\\n)/', $message, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = array('start-line' => array_shift($lines), 'headers' => array(), 'body' => '');
        array_shift($lines);

        for ($i = 0, $totalLines = count($lines); $i < $totalLines; $i += 2) {
            $line = $lines[$i];
            // If two line breaks were encountered, then this is the end of body
            if (empty($line)) {
                if ($i < $totalLines - 1) {
                    $result['body'] = implode('', array_slice($lines, $i + 2));
                }
                break;
            }
            if (strpos($line, ':')) {
                $parts = explode(':', $line, 2);
                $key = trim($parts[0]);
                $value = isset($parts[1]) ? trim($parts[1]) : '';
                $result['headers'][$key][] = $value;
            }
        }

        return $result;
    }
}
