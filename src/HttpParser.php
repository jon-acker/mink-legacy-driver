<?php

namespace Jacker\LegacyDriver;

use Symfony\Component\BrowserKit\Response;

final class HttpParser
{
    const HTTP_VERSION = 'HTTP/1.1';
    const DEFAULT_STATUS_CODE = '200 OK';

    /**
     * This code is based on https://github.com/guzzle/psr7/blob/41972f428b31bc3ebff0707f63dd2165d3ac4cf6/src/functions.php#L491-L506
     *
     * @param string $message
     *
     * @return Response
     */
    public static function createResponseFrom($message)
    {
        $message = preg_replace('/^Status:/', self::HTTP_VERSION, $message, 1);
        if (strpos($message, self::HTTP_VERSION) !== 0) {
            $message = self::HTTP_VERSION . ' ' . self::DEFAULT_STATUS_CODE . "\r\n" . $message;
        }

        $data = self::parseMessage($message);
        $parts = explode(' ', $data['start-line'], 3);

        return new Response(
            $data['body'],
            $parts[1],
            $data['headers']
        );
    }

    /**
     * This code is https://github.com/guzzle/psr7/blob/41972f428b31bc3ebff0707f63dd2165d3ac4cf6/src/functions.php#L752-L781
     *
     * @param string $message
     *
     * @return array
     */
    private static function parseMessage($message)
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
