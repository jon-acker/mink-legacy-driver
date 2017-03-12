<?php

namespace spec\carlosV2\LegacyDriver\LegacyApp;

use carlosV2\LegacyDriver\LegacyApp\Controllers;
use carlosV2\LegacyDriver\LegacyApp\LegacyApp;
use carlosV2\LegacyDriver\LegacyApp\Script;
use PhpSpec\ObjectBehavior;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * @mixin LegacyApp
 */
class LegacyAppSpec extends ObjectBehavior
{
    function let(Script $bootstrapScript, Controllers $controllers)
    {
        $bootstrapScript->__toString()->willReturn('script.php');
        $documentRoot = './';
        $environmentVariables = [];
        $bootstrapScripts = [];
        $this->beConstructedWith($documentRoot, $controllers, $environmentVariables, $bootstrapScripts);
    }

    function it_loads_all_bootstrap_scripts(Script $bootstrapScript, Controllers $controllers)
    {
        $request = new Request('http://localhost/', 'GET');
        $controllers->getFront($request)->willReturn($bootstrapScript);

        $bootstrapScript->load()->shouldBeCalled();

        $this->handle($request);

    }

    function xit_sets_up_get_parameters_from_query_string()
    {
        $request = new Request('http://localhost/', 'GET');
        $this->handle($request);
    }
}
