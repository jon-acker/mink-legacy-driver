<?php

namespace spec\carlosV2\LegacyDriver\LegacyApp;

use PhpSpec\ObjectBehavior;

class ScriptSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['bootstrap.php']);
    }
}
