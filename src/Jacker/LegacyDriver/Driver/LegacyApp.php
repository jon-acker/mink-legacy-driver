<?php

namespace Jacker\LegacyDriver\Driver;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LegacuApp implements HttpKernelInterface
{
    /**
     * @var Container
     */
    private $container;

    function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * @param Request $request A Request instance
     *
     * @return Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $_SERVER = [];
        $_SERVER['REQUEST_URI'] = $request->getRequestUri();
        $_SERVER['QUERY_STRING'] = $request->getQueryString();
        $_SERVER['REQUEST_METHOD'] = $request->getMethod();
        $_SERVER['REQUEST_METHOD'] = $request->getContentType();
        $_SERVER['CONTENT_TYPE'] = $request->getContentType();
        $_SERVER['HTTP_HOST'] = $request->getHttpHost();
        $_SERVER['MAGE_MODE'] = 'developer';
        parse_str($_SERVER['QUERY_STRING'], $_GET);

        $contents = $this->runApplication();

        return new Response($contents, 200);
    }

    /**
     * Gets the current container.
     *
     * @return ContainerInterface A ContainerInterface instance
     *
     * @api
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return string
     */
    private function runApplication()
    {
        ob_start();
        define ('BP', './');

        include BP . '/app/functions.php';
        $composerAutoloader = include BP . "/vendor/autoload.php";

        AutoloaderRegistry::registerAutoloader(new ClassLoaderWrapper($composerAutoloader));

        Bootstrap::populateAutoloader(BP, []);

        $params = $_SERVER;

        $params[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS] = [
            DirectoryList::PUB => [DirectoryList::URL_PATH => ''],
            DirectoryList::MEDIA => [DirectoryList::URL_PATH => 'media'],
            DirectoryList::STATIC_VIEW => [DirectoryList::URL_PATH => 'static'],
            DirectoryList::UPLOAD => [DirectoryList::URL_PATH => 'media/upload'],
        ];
        $bootstrap = Bootstrap::create(BP, $params);
//        /** @var \Magento\Framework\App\Http $app */
        $app = $bootstrap->createApplication('Magento\Framework\App\Http');
        $bootstrap->run($app);
//
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}
