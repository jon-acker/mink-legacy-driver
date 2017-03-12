<?php

namespace spec\carlosV2\LegacyDriver\LegacyApp;

use carlosV2\LegacyDriver\LegacyApp\Controllers;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\RouteCollection;

class ControllersSpec extends ObjectBehavior
{
    function it_is_constructed_with_routes(RouteCollection $routeCollection)
    {

        $this->beConstructedWith($routeCollection);
    }
}
