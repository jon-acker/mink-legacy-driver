<?php

namespace spec\carlosV2\LegacyDriver\LegacyApp;

use carlosV2\LegacyDriver\LegacyApp\Controllers;
use carlosV2\LegacyDriver\LegacyApp\LegacyApp;
use carlosV2\LegacyDriver\LegacyApp\Script;
use PhpSpec\ObjectBehavior;
use PHPUnit_Framework_Assert;
use Symfony\Component\BrowserKit\Request;

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
        $bootstrapScripts = [$bootstrapScript];

        $bootstrapScript->load()->willReturn();

        $this->beConstructedWith($documentRoot, $controllers, $environmentVariables, $bootstrapScripts);
    }

    function it_loads_all_bootstrap_scripts(Script $bootstrapScript, Controllers $controllers)
    {
        $request = new Request('http://localhost/', 'GET');
        $controllers->getFront($request)->willReturn($bootstrapScript);

        $bootstrapScript->load()->shouldBeCalledTimes(2);

        $this->handle($request);
    }

    function it_sets_up_request_parameters_from_query_string_on_GET(Script $bootstrapScript, Controllers $controllers)
    {
        $request = new Request('http://localhost/?name=jon', 'GET');
        $controllers->getFront($request)->willReturn($bootstrapScript);

        $this->handle($request);

        PHPUnit_Framework_Assert::assertEquals($_REQUEST['name'], 'jon');
    }
}
