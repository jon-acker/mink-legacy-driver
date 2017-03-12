<?php

namespace carlosV2\LegacyDriver\LegacyApp;

use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Controllers
{
    /**
     * @var RouteCollection
     */
    private $routeCollection;

    /**
     * @param RouteCollection $routeCollection
     */
    public function __construct(RouteCollection $routeCollection)
    {
        $this->routeCollection = $routeCollection;
    }

    /**
     * @param Request $request
     *
     * @return Script
     */
    public function getFront(Request $request)
    {
        $parts = parse_url($request->getUri());

        $matcher = new UrlMatcher(
            $this->routeCollection,
            new RequestContext(
                '/',
                strtoupper($request->getMethod())
            )
        );

        $parameters = $matcher->match($parts['path']);

        return new Script($parameters['file']);
    }

    public function add(Route $route)
    {
        $this->routeCollection->add(md5(serialize($route)), $route);
    }
}
