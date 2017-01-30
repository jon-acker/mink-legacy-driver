<?php
namespace Jacker\LegacyDriver\Driver;

use Behat\Mink\Driver\BrowserKitDriver;
use Jacker\LegacyDriver\Client\TestClient;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LegacyAppDriver extends BrowserKitDriver
{
    public function __construct(HttpKernelInterface $kernel, $baseUrl = null)
    {
        $_SERVER['HTTP_HOST'] = basename($baseUrl);
        parent::__construct($kernel->getContainer()->get(TestClient::SERVICE_ID), $baseUrl);
    }
}
